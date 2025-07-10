<?php

namespace App\Http\Controllers;

use App\Models\AssessmentForm;
use App\Models\Assessment;
use App\Models\Factor;
use App\Models\Question;
use App\Models\Response;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function showForm($flow = null)
    {
        // Get all active factors for navigation tabs
        $factors = Factor::where('is_active', true)->orderBy('name')->get();
        
        // Get current factor (default to first factor)
        $currentFactorSlug = request()->get('factor', $factors->first()->slug ?? 'it-infrastructure');
        $currentFactor = Factor::where('slug', $currentFactorSlug)->first();
        
        if (!$currentFactor) {
            $currentFactor = $factors->first();
        }
        
        // Get questions for current factor
        $questions = Question::where('factor_id', $currentFactor->id)
                            ->where('is_active', true)
                            ->orderBy('order')
                            ->orderBy('created_at')
                            ->get();
        
        // Get current assessment if exists (in progress)
        $currentAssessment = Assessment::where('user_id', Auth::id())
                                     ->where('status', 'in_progress')
                                     ->latest()
                                     ->first();
        
        // Get existing responses for current assessment
        $existingResponses = [];
        if ($currentAssessment) {
            $responses = Response::where('assessment_id', $currentAssessment->id)
                               ->whereIn('question_id', $questions->pluck('id'))
                               ->get()
                               ->keyBy('question_id');
            
            foreach ($responses as $response) {
                $existingResponses[$response->question_id] = $response;
            }
        }
        
        // Calculate progress
        $totalQuestions = Question::where('is_active', true)->count();
        $answeredQuestions = 0;
        
        if ($currentAssessment) {
            $answeredQuestions = Response::where('assessment_id', $currentAssessment->id)->count();
        }
        
        $progressPercentage = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;
        
        // Get user's department
        $userDepartment = Auth::user()->department ?? Department::where('slug', 'overall')->first();
        
        return view('assessment.form', compact(
            'factors',
            'currentFactor',
            'questions',
            'existingResponses',
            'currentAssessment',
            'progressPercentage',
            'answeredQuestions',
            'totalQuestions',
            'userDepartment',
            'flow'
        ));
    }

    public function saveResponse(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'score' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();
            
            // Get or create current assessment
            $assessment = Assessment::where('user_id', Auth::id())
                                  ->where('status', 'in_progress')
                                  ->latest()
                                  ->first();
            
            if (!$assessment) {
                $assessment = Assessment::create([
                    'user_id' => Auth::id(),
                    'department_id' => Auth::user()->department_id ?? Department::where('slug', 'overall')->first()->id,
                    'assessment_date' => now(),
                    'status' => 'in_progress',
                    'target_level' => 5.0,
                    'readiness_level' => 0,
                    'total_score' => 0
                ]);
            }
            
            // Update or create response
            $response = Response::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'question_id' => $request->question_id,
                    'user_id' => Auth::id()
                ],
                [
                    'score' => $request->score,
                    'comment' => $request->comment
                ]
            );
            
            // Recalculate assessment totals
            $this->recalculateAssessmentScores($assessment);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Response saved successfully',
                'weighted_score' => $response->score * $response->question->weight,
                'assessment_id' => $assessment->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFactorQuestions(Factor $factor)
    {
        $questions = Question::where('factor_id', $factor->id)
                           ->where('is_active', true)
                           ->orderBy('order')
                           ->orderBy('created_at')
                           ->get();
        
        // Get current assessment responses for this factor
        $assessment = Assessment::where('user_id', Auth::id())
                                ->where('status', 'in_progress')
                                ->latest()
                                ->first();
        
        $existingResponses = [];
        if ($assessment) {
            $responses = Response::where('assessment_id', $assessment->id)
                               ->whereIn('question_id', $questions->pluck('id'))
                               ->get()
                               ->keyBy('question_id');
            
            foreach ($responses as $response) {
                $existingResponses[$response->question_id] = [
                    'score' => $response->score,
                    'comment' => $response->comment,
                    'weighted_score' => $response->score * $response->question->weight
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'factor' => $factor,
            'questions' => $questions,
            'existing_responses' => $existingResponses
        ]);
    }

    public function submitAssessment(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Get current assessment
            $assessment = Assessment::where('user_id', Auth::id())
                                  ->where('status', 'in_progress')
                                  ->latest()
                                  ->first();
            
            if (!$assessment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assessment in progress found'
                ], 404);
            }
            
            // Check if all questions are answered
            $totalQuestions = Question::where('is_active', true)->count();
            $answeredQuestions = Response::where('assessment_id', $assessment->id)->count();
            
            if ($answeredQuestions < $totalQuestions) {
                return response()->json([
                    'success' => false,
                    'message' => "Please answer all questions. {$answeredQuestions} of {$totalQuestions} completed."
                ], 400);
            }
            
            // Finalize assessment
            $assessment->update([
                'status' => 'completed',
                'assessment_date' => now()
            ]);
            
            // Recalculate final scores
            $this->recalculateAssessmentScores($assessment);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Assessment completed successfully!',
                'assessment_id' => $assessment->id,
                'readiness_level' => $assessment->readiness_level,
                'redirect_url' => route('dashboard.historical')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error submitting assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProgress()
    {
        $assessment = Assessment::where('user_id', Auth::id())
                                ->where('status', 'in_progress')
                                ->latest()
                                ->first();
        
        $totalQuestions = Question::where('is_active', true)->count();
        $answeredQuestions = 0;
        
        if ($assessment) {
            $answeredQuestions = Response::where('assessment_id', $assessment->id)->count();
        }
        
        $progressPercentage = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;
        
        return response()->json([
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'progress_percentage' => $progressPercentage,
            'assessment_id' => $assessment->id ?? null
        ]);
    }

    public function showResults(Assessment $assessment)
    {
        // Ensure user can only view their own assessments
        if ($assessment->user_id !== Auth::id()) {
            abort(403);
        }
        
        $responses = $assessment->responses()->with(['question.factor'])->get();
        $responsesByFactor = $responses->groupBy('question.factor.name');
        
        return view('assessment.results', compact('assessment', 'responsesByFactor'));
    }

    public function assessmentHistory()
    {
        try {
            // Get all completed assessments for the current user
            $assessments = Assessment::where('user_id', Auth::id())
                                    ->where('status', 'completed')
                                    ->with(['department', 'user'])
                                    ->orderBy('assessment_date', 'desc')
                                    ->paginate(10);
            
            return view('dashboard.historical', compact('assessments'));
            
        } catch (\Exception $e) {
            // If there's an error, return view with empty collection
            $assessments = Assessment::where('id', 0)->paginate(10); // Empty pagination
            return view('dashboard.historical', compact('assessments'));
        }
    }

    private function recalculateAssessmentScores(Assessment $assessment)
    {
        $responses = $assessment->responses()->with('question')->get();
        
        if ($responses->isEmpty()) {
            $assessment->update([
                'readiness_level' => 0,
                'total_score' => 0
            ]);
            return;
        }
        
        // Calculate weighted average
        $totalWeightedScore = 0;
        $totalWeight = 0;
        
        foreach ($responses as $response) {
            $weight = $response->question->weight ?? 1;
            $totalWeightedScore += $response->score * $weight;
            $totalWeight += $weight;
        }
        
        $averageScore = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
        
        $assessment->update([
            'readiness_level' => round($averageScore, 1),
            'total_score' => round($averageScore, 1)
        ]);
    }

    public function downloadAssessment(Assessment $assessment)
    {
        // Ensure user can only download their own assessments
        if ($assessment->user_id !== Auth::id()) {
            abort(403);
        }
        
        $responses = $assessment->responses()->with(['question.factor'])->get();
        $responsesByFactor = $responses->groupBy('question.factor.name');
        
        // Generate PDF or Excel report
        // For now, return a view that can be printed
        return view('assessment.download', compact('assessment', 'responsesByFactor'));
    }

    public function editAssessment(Assessment $assessment)
    {
        // Ensure user can only edit their own assessments and within time limit
        if ($assessment->user_id !== Auth::id()) {
            abort(403);
        }
        
        if ($assessment->created_at->diffInDays(now()) > 7) {
            return redirect()->route('assessment.history')->with('error', 'Assessment can only be edited within 7 days of creation.');
        }
        
        // Create a new assessment based on the existing one
        $newAssessment = Assessment::create([
            'user_id' => Auth::id(),
            'department_id' => $assessment->department_id,
            'assessment_date' => now(),
            'status' => 'in_progress',
            'target_level' => $assessment->target_level,
            'readiness_level' => 0,
            'total_score' => 0
        ]);
        
        // Copy responses from original assessment
        $originalResponses = $assessment->responses()->with('question')->get();
        foreach ($originalResponses as $response) {
            Response::create([
                'assessment_id' => $newAssessment->id,
                'question_id' => $response->question_id,
                'user_id' => Auth::id(),
                'score' => $response->score,
                'comment' => $response->comment
            ]);
        }
        
        // Recalculate scores
        $this->recalculateAssessmentScores($newAssessment);
        
        return redirect()->route('assessment.form')->with('success', 'Assessment copied for editing. You can now modify your responses.');
    }

    public function getAssessmentDetails(Assessment $assessment)
    {
        // Ensure user can only view their own assessments
        if ($assessment->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $assessment->load(['department', 'user']);
        $responses = $assessment->responses()->with(['question.factor'])->get();
        $responsesByFactor = $responses->groupBy('question.factor.name');
        
        return response()->json([
            'success' => true,
            'assessment' => $assessment,
            'responses_by_factor' => $responsesByFactor
        ]);
    }

    public function deleteAssessment(Assessment $assessment)
    {
        // Ensure user can only delete their own assessments
        if ($assessment->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        try {
            // Delete responses first
            $assessment->responses()->delete();
            
            // Delete assessment
            $assessment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Assessment deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting assessment: ' . $e->getMessage()
            ], 500);
        }
    }
}