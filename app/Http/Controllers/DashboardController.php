<?php
// app/Http/Controllers/DashboardController.php - Simplified factorDashboard method

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\Factor;
use App\Models\Question;
use App\Models\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $departmentSlug = $request->get('department', 'overall');
        $department = $departmentSlug === 'overall' ? null : Department::where('slug', $departmentSlug)->first();
        $departmentId = $department?->id;
        
        $currentReadiness = $this->getCurrentReadinessLevel($departmentId);
        $aggregatedReadiness = $this->getAggregatedReadiness($departmentId);
        $factorReadiness = $this->getFactorReadiness($departmentId);
        $historicalData = $this->getHistoricalData($departmentId);
        $factors = Factor::where('is_active', true)->orderBy('name')->get();

        $departments = Department::all();
        
        return view('dashboard.index', compact(
            'currentReadiness',
            'aggregatedReadiness', 
            'factorReadiness',
            'historicalData',
            'departments',
            'factors'
        ));
    }

    public function factorDashboard(Request $request, $factorSlug = null)
    {
        // Get factor from route parameter, fallback to IT Infrastructure
        $factorSlug = $factorSlug ?? 'it-infrastructure';
        
        // Find the factor
        $factor = Factor::where('slug', $factorSlug)->first();
        
        // If factor not found, redirect to first available factor
        if (!$factor) {
            $factor = Factor::where('is_active', true)->first();
            if ($factor) {
                return redirect()->route('dashboard.factor', ['factor' => $factor->slug]);
            }
            // If no factors exist, create a default one
            abort(404, 'No factors found');
        }
        
        // Get all factors for the dropdown
        $factors = Factor::where('is_active', true)->get();
        
        // Get current assessment for this factor (overall department for now)
        $currentAssessment = $this->getFactorAssessment($factor->id, null);
        
        // Get questions for this factor with their current scores
        $questions = $this->getFactorQuestions($factor->id, null);
        
        // Get historical data for this factor
        $historicalData = $this->getFactorHistoricalData($factor->id, null);
        
        return view('dashboard.factor', compact(
            'currentAssessment', 
            'questions', 
            'historicalData', 
            'factor',
            'factors'
        ));
    }

    public function historicalAssessment(Request $request)
    {
        $departments = Department::all();
        $selectedDepartment = $request->get('department', 'overall');
        
        $department = $selectedDepartment === 'overall' ? null : Department::where('slug', $selectedDepartment)->first();
        
        $historicalData = $this->getHistoricalData($department?->id);
        
        return view('dashboard.historical', compact(
            'departments', 
            'selectedDepartment', 
            'historicalData'
        ));
    }

    // Private helper methods
    private function getCurrentReadinessLevel($departmentId = null)
    {
        $query = Assessment::query();
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $latestAssessment = $query->where('status', 'completed')
                                 ->latest('assessment_date')
                                 ->first();
        
        if (!$latestAssessment) {
            return (object) [
                'readiness_level' => 0,
                'target_level' => 4.0
            ];
        }
        
        $this->recalculateAssessmentScore($latestAssessment);
        
        return $latestAssessment;
    }

    private function getAggregatedReadiness($departmentId = null)
    {
        $factors = Factor::where('is_active', true)->get();
        $aggregated = [];
        
        foreach ($factors as $factor) {
            $level = $this->getFactorReadinessLevel($factor->id, $departmentId);
            $aggregated[$factor->name] = $level;
        }
        
        return $aggregated;
    }

    private function getFactorReadiness($departmentId = null)
    {
        $factors = Factor::where('is_active', true)->get();
        $factorReadiness = [];
        
        foreach ($factors as $factor) {
            $level = $this->getFactorReadinessLevel($factor->id, $departmentId);
            $factorReadiness[] = [
                'name' => $factor->name,
                'level' => $level,
                'color' => $this->getColorClass($level),
                'slug' => $factor->slug
            ];
        }
        
        return $factorReadiness;
    }

    private function getFactorReadinessLevel($factorId, $departmentId = null)
    {
        // Get latest assessment's responses for this factor
        $latestAssessment = Assessment::query();
        if ($departmentId) {
            $latestAssessment->where('department_id', $departmentId);
        }
        $latestAssessment = $latestAssessment->where('status', 'completed')
                                           ->latest('assessment_date')
                                           ->first();

        if (!$latestAssessment) {
            return 0;
        }

        // Get responses for this factor from the latest assessment
        $responses = Response::where('assessment_id', $latestAssessment->id)
                           ->whereHas('question', function($q) use ($factorId) {
                               $q->where('factor_id', $factorId);
                           })
                           ->with('question')
                           ->get();
        
        if ($responses->isEmpty()) {
            return 0;
        }

        // Calculate weighted average
        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($responses as $response) {
            $weight = $response->question->weight ?? 1;
            $totalWeightedScore += $response->score * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 1) : 0;
    }

    private function getHistoricalData($departmentId = null)
    {
        $query = Assessment::query();
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        // Get assessments, group by date, and take only the latest per date
        $assessments = $query->where('status', 'completed')
                            ->orderBy('assessment_date', 'desc')
                            ->get()
                            ->groupBy(function($assessment) {
                                return $assessment->assessment_date->format('Y-m-d');
                            })
                            ->map(function($group) {
                                // Take only the latest assessment for each date
                                return $group->first();
                            })
                            ->take(6) // Limit to 6 data points for readability
                            ->sortBy('assessment_date')
                            ->values();
        
        $historicalData = [];
        
        foreach ($assessments as $assessment) {
            $this->recalculateAssessmentScore($assessment);
            $historicalData[] = [
                'date' => $assessment->assessment_date->format('M Y'), // Better date format
                'readiness_level' => $assessment->readiness_level,
                'target_level' => $assessment->target_level ?? 4.0
            ];
        }
        
        return $historicalData;
    }

    private function getFactorAssessment($factorId, $departmentId = null)
    {
        $readinessLevel = $this->getFactorReadinessLevel($factorId, $departmentId);
        
        return (object) [
            'readiness_level' => $readinessLevel,
            'target_level' => 4.0
        ];
    }

    private function getFactorQuestions($factorId, $departmentId = null)
    {
        $questions = Question::where('factor_id', $factorId)
                           ->where('is_active', true)
                           ->orderBy('order')
                           ->orderBy('created_at')
                           ->get();
        
        return $questions->map(function ($question) use ($departmentId) {
            $currentScore = $this->getQuestionCurrentScore($question->id, $departmentId);
            
            return (object) [
                'id' => $question->id,
                'question' => $question->question,
                'current_score' => $currentScore,
                'weight' => $question->weight ?? 1
            ];
        });
    }

    private function getQuestionCurrentScore($questionId, $departmentId = null)
    {
        // Get the latest assessment
        $latestAssessment = Assessment::query();
        if ($departmentId) {
            $latestAssessment->where('department_id', $departmentId);
        }
        $latestAssessment = $latestAssessment->where('status', 'completed')
                                           ->latest('assessment_date')
                                           ->first();

        if (!$latestAssessment) {
            return 0;
        }

        // Get response for this question from the latest assessment
        $response = Response::where('assessment_id', $latestAssessment->id)
                          ->where('question_id', $questionId)
                          ->first();
        
        return $response ? $response->score : 0;
    }

    private function getFactorHistoricalData($factorId, $departmentId = null)
    {
        $query = Assessment::query();
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        // Get unique assessments by date, limited to recent ones
        $assessments = $query->where('status', 'completed')
                            ->orderBy('assessment_date', 'desc')
                            ->take(6) // Limit to 6 most recent assessments
                            ->get()
                            ->unique('assessment_date') // Remove duplicates by date
                            ->sortBy('assessment_date'); // Sort chronologically
        
        $historicalData = [];
        
        foreach ($assessments as $assessment) {
            // Calculate factor average for this assessment
            $responses = Response::where('assessment_id', $assessment->id)
                            ->whereHas('question', function($q) use ($factorId) {
                                $q->where('factor_id', $factorId);
                            })
                            ->with('question')
                            ->get();
            
            if ($responses->isNotEmpty()) {
                $totalWeightedScore = 0;
                $totalWeight = 0;
                
                foreach ($responses as $response) {
                    $weight = $response->question->weight ?? 1;
                    $totalWeightedScore += $response->score * $weight;
                    $totalWeight += $weight;
                }
                
                $avgScore = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
                
                $historicalData[] = [
                    'date' => $assessment->assessment_date->format('M j'), // Shorter format: "Oct 4"
                    'readiness_level' => round($avgScore, 1),
                    'target_level' => 4.0
                ];
            }
        }
        
        return $historicalData;
    }

    private function recalculateAssessmentScore($assessment)
    {
        $responses = $assessment->responses()->with('question')->get();
        
        if ($responses->isEmpty()) {
            $assessment->update([
                'readiness_level' => 0,
                'total_score' => 0
            ]);
            return;
        }
        
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

    private function getColorClass($level)
    {
        if ($level >= 3.76) {
            return 'green-500';
        } elseif ($level >= 2.51) {
            return 'yellow-500';
        } elseif ($level >= 1.26) {
            return 'orange-500';
        } else {
            return 'red-500';
        }
    }

    // AJAX Methods (keeping for compatibility)
    public function getHistoricalDataAjax(Request $request)
    {
        $filterType = $request->get('filter_type', 'overall'); // 'overall' or factor slug
        
        if ($filterType === 'overall') {
            // Get overall historical data across all factors
            $historicalData = $this->getOverallHistoricalData();
        } else {
            // Get historical data for specific factor
            $factor = Factor::where('slug', $filterType)->first();
            if ($factor) {
                $historicalData = $this->getFactorHistoricalData($factor->id, null);
            } else {
                $historicalData = [];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $historicalData
        ]);
    }

    private function getOverallHistoricalData()
    {
        $assessments = Assessment::where('status', 'completed')
                                ->orderBy('assessment_date', 'desc')
                                ->get()
                                ->groupBy(function($assessment) {
                                    return $assessment->assessment_date->format('Y-m-d');
                                })
                                ->map(function($group) {
                                    return $group->first();
                                })
                                ->take(6)
                                ->sortBy('assessment_date')
                                ->values();
        
        $historicalData = [];
        
        foreach ($assessments as $assessment) {
            $this->recalculateAssessmentScore($assessment);
            $historicalData[] = [
                'date' => $assessment->assessment_date->format('M Y'),
                'readiness_level' => $assessment->readiness_level,
                'target_level' => $assessment->target_level ?? 5.0
            ];
        }
        
        return $historicalData;
    }

    public function getFactorQuestionsAjax(Request $request)
    {
        $factorSlug = $request->get('factor');
        $departmentSlug = $request->get('department', 'overall');
        
        $factor = Factor::where('slug', $factorSlug)->first();
        $department = $departmentSlug === 'overall' ? null : Department::where('slug', $departmentSlug)->first();
        
        if (!$factor) {
            return response()->json([
                'success' => false,
                'message' => 'Factor not found'
            ], 404);
        }
        
        $questions = $this->getFactorQuestions($factor->id, $department?->id);
        
        return response()->json([
            'success' => true,
            'questions' => $questions,
            'factor' => $factor,
            'department' => $department
        ]);
    }

    public function getCorrectiveActionsAjax(Request $request)
    {
        $factorSlug = $request->get('factor');
        $departmentSlug = $request->get('department', 'overall');
        
        $factor = Factor::where('slug', $factorSlug)->first();
        $department = $departmentSlug === 'overall' ? null : Department::where('slug', $departmentSlug)->first();
        
        if (!$factor) {
            return response()->json([
                'success' => false,
                'message' => 'Factor not found'
            ], 404);
        }
        
        $correctiveActionsQuery = \App\Models\CorrectiveAction::whereHas('question', function($q) use ($factor) {
            $q->where('factor_id', $factor->id);
        })->with('question');
        
        $correctiveActions = $correctiveActionsQuery->get();
        
        $formattedActions = $correctiveActions->map(function($action) {
            return [
                'id' => $action->id,
                'question_id' => $action->question_id,
                'action' => $action->action,
                'department' => $action->department ?? 'All Departments',
                'question' => $action->question ? $action->question->question : ''
            ];
        });
        
        return response()->json([
            'success' => true,
            'corrective_actions' => $formattedActions,
            'factor' => $factor,
            'department' => $department
        ]);
    }

    public function getFactorHistoricalDataAjax(Request $request)
    {
        $factorSlug = $request->get('factor');
        $departmentSlug = $request->get('department', 'overall');
        
        $factor = Factor::where('slug', $factorSlug)->first();
        $department = $departmentSlug === 'overall' ? null : Department::where('slug', $departmentSlug)->first();
        
        if (!$factor) {
            return response()->json([
                'success' => false,
                'message' => 'Factor not found'
            ], 404);
        }
        
        $historicalData = $this->getFactorHistoricalData($factor->id, $department?->id);
        
        return response()->json([
            'success' => true,
            'data' => $historicalData,
            'factor' => $factor,
            'department' => $department
        ]);
    }
}