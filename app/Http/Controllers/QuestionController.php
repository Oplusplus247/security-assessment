<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Factor;
use App\Models\CorrectiveAction;
use App\Models\QuestionTracking;
use App\Models\AssessmentToken;
use App\Mail\AssessmentInvitation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    public function trackQuestions(Request $request)
    {
        $query = QuestionTracking::query();
        
        // Apply filters if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('email') && $request->email) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        
        // Get paginated results
        $tracking = $query->orderBy('date', 'desc')->paginate(10);
        
        // Get status counts for stats
        $statusCounts = QuestionTracking::getStatusCounts();
        
        return view('questions.track', compact('tracking', 'statusCounts'));
    }

    public function editQuestions(Request $request)
    {
        $selectedFactor = $request->get('factor', 'ir-plan');
        
        // Get all factors for dropdown
        $factors = Factor::where('is_active', true)->get();
        
        // Get current factor
        $currentFactor = Factor::where('slug', $selectedFactor)->first();
        if (!$currentFactor) {
            $currentFactor = $factors->first();
        }
        
        // Get questions for selected factor with pagination
        $questions = Question::where('factor_id', $currentFactor->id)
                            ->where('is_active', true)
                            ->orderBy('created_at')
                            ->paginate(10);
        
        return view('questions.edit', compact('questions', 'factors', 'currentFactor'));
    }

    public function sendQuestions()
    {
        // Get all active factors for dropdown
        $factors = Factor::where('is_active', true)->with(['questions' => function($query) {
            $query->where('is_active', true);
        }])->get();
        
        // Get questions grouped by factor for preview
        $questions = Question::with('factor')
                            ->where('is_active', true)
                            ->orderBy('factor_id')
                            ->get()
                            ->groupBy('factor.name');
        
        // Get status counts for statistics
        $statusCounts = QuestionTracking::getStatusCounts();
        
        return view('questions.send', compact('questions', 'factors', 'statusCounts'));
    }

    public function getFactorQuestions(Factor $factor)
    {
        $questions = Question::where('factor_id', $factor->id)
                           ->where('is_active', true)
                           ->orderBy('order')
                           ->orderBy('created_at')
                           ->get();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'questions' => $questions,
                'factor' => $factor
            ]);
        }
        
        return view('questions.edit', compact('questions', 'factor'));
    }

    public function sendQuestionsToEmails(Request $request)
    {
        $request->validate([
            'emails' => 'required|array|min:1',
            'emails.*' => 'email',
            'factor_id' => 'required|exists:factors,id',
            'questions' => 'required|array|min:1',
            'include_instructions' => 'boolean',
            'send_reminder' => 'boolean'
        ]);

        try {
            $factor = Factor::find($request->factor_id);
            $questions = Question::whereIn('id', $request->questions)
                               ->where('is_active', true)
                               ->get();

            if ($questions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid questions found for the selected factor.'
                ], 400);
            }

            $sentCount = 0;
            
            foreach ($request->emails as $email) {
                try {
                    // Create tracking record
                    $tracking = QuestionTracking::create([
                        'date' => now(),
                        'assessment_type' => $factor->name . ' Assessment',
                        'email' => $email,
                        'status' => 'sent'
                    ]);

                    // Send email (implement your email logic here)
                    $this->sendAssessmentEmail($email, $factor, $questions, [
                        'include_instructions' => $request->include_instructions ?? false,
                        'send_reminder' => $request->send_reminder ?? false,
                        'tracking_id' => $tracking->id
                    ]);

                    $sentCount++;
                    
                } catch (\Exception $e) {
                    Log::error("Failed to send assessment to {$email}: " . $e->getMessage());
                    // Update tracking status to failed
                    if (isset($tracking)) {
                        $tracking->update(['status' => 'failed']);
                    }
                }
            }

            if ($sentCount > 0) {
                return response()->json([
                    'success' => true, 
                    'message' => "Assessment questions sent successfully to {$sentCount} recipient(s)!"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send questions to any recipients. Please check the logs.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error sending questions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending questions: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendAssessmentEmail($email, $factor, $questions, $options = [])
    {
        // Create assessment token
        $expiresAt = now()->addDays(30);
        $assessmentToken = AssessmentToken::createForAssessment(
            $email,
            $factor->id,
            $questions->pluck('id')->toArray(),
            [
                'tracking_id' => $options['tracking_id'] ?? null,
                'expires_at' => $expiresAt,
                'metadata' => [
                    'include_instructions' => $options['include_instructions'] ?? false,
                    'send_reminder' => $options['send_reminder'] ?? false
                ]
            ]
        );

        // Generate assessment URL
        $assessmentUrl = route('assessment.public', ['token' => $assessmentToken->token]);

        // Prepare email data
        $emailData = [
            'recipient_email' => $email,
            'factor' => $factor,
            'questions' => $questions,
            'assessment_url' => $assessmentUrl,
            'include_instructions' => $options['include_instructions'] ?? false,
            'tracking_id' => $options['tracking_id'] ?? null,
            'expires_at' => $expiresAt
        ];

        try {
            // Send the email using the Mailable class
            Mail::to($email)->send(new \App\Mail\AssessmentInvitation($emailData));
            
            Log::info("Assessment email sent successfully to: {$email} for factor: {$factor->name}");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to send assessment email to {$email}: " . $e->getMessage());
            
            // Mark token as invalid if email fails
            $assessmentToken->delete();
            
            throw $e;
        }
    }

    public function storeQuestion(Request $request)
    {
        // Always return JSON for AJAX requests, even on validation errors
        if ($request->wantsJson() || $request->ajax()) {
            try {
                $validator = Validator::make($request->all(), [
                    'question' => 'required|string|max:500',
                    'factor_id' => 'required|exists:factors,id',
                    'weight' => 'required|integer|min:1|max:5',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed: ' . $validator->errors()->first(),
                        'errors' => $validator->errors()
                    ], 422);
                }

                $question = Question::create([
                    'factor_id' => $request->factor_id,
                    'question' => $request->question,
                    'weight' => $request->weight,
                    'is_active' => true
                ]);

                return response()->json([
                    'success' => true, 
                    'message' => 'Question created successfully!',
                    'question' => $question->load('factor')
                ]);

            } catch (\Exception $e) {
                Log::error('Error creating question: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating question: ' . $e->getMessage()
                ], 500);
            }
        }

        // For non-AJAX requests, use traditional validation
        $request->validate([
            'question' => 'required|string|max:500',
            'factor_id' => 'required|exists:factors,id',
            'weight' => 'required|integer|min:1|max:5',
        ]);

        $question = Question::create([
            'factor_id' => $request->factor_id,
            'question' => $request->question,
            'weight' => $request->weight,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Question created successfully!');
    }

    public function updateQuestion(Request $request, Question $question)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'factor_id' => 'required|exists:factors,id',
            'weight' => 'required|integer|min:1|max:5',
        ]);

        $question->update([
            'question' => $request->question,
            'factor_id' => $request->factor_id,
            'weight' => $request->weight,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true, 
                'message' => 'Question updated successfully!',
                'question' => $question->load('factor')
            ]);
        }

        return redirect()->back()->with('success', 'Question updated successfully!');
    }

    public function destroyQuestion(Question $question)
    {
        // Soft delete by setting is_active to false
        $question->update(['is_active' => false]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Question deleted successfully!'
        ]);
    }

    public function bulkUpdateQuestions(Request $request)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.question' => 'required|string|max:500',
            'questions.*.weight' => 'required|integer|min:1|max:5',
        ]);

        foreach ($request->questions as $questionData) {
            Question::where('id', $questionData['id'])->update([
                'question' => $questionData['question'],
                'weight' => $questionData['weight'],
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Questions updated successfully!'
        ]);
    }

    public function duplicateQuestion(Question $question)
    {
        $duplicatedQuestion = Question::create([
            'factor_id' => $question->factor_id,
            'question' => $question->question . ' (Copy)',
            'weight' => $question->weight,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Question duplicated successfully!',
            'question' => $duplicatedQuestion->load('factor')
        ]);
    }

    public function reorderQuestions(Request $request)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.order' => 'required|integer',
        ]);

        foreach ($request->questions as $questionData) {
            Question::where('id', $questionData['id'])->update([
                'order' => $questionData['order']
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Questions reordered successfully!'
        ]);
    }

    // Additional helper methods for email functionality

    public function resendQuestions(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|exists:question_tracking,id'
        ]);

        $tracking = QuestionTracking::find($request->tracking_id);
        
        // Extract factor info and resend
        $factorName = str_replace(' Assessment', '', $tracking->assessment_type);
        $factor = Factor::where('name', $factorName)->first();
        
        if ($factor) {
            $questions = Question::where('factor_id', $factor->id)
                               ->where('is_active', true)
                               ->get();
            
            $this->sendAssessmentEmail($tracking->email, $factor, $questions, [
                'tracking_id' => $tracking->id
            ]);
            
            $tracking->update([
                'date' => now(),
                'status' => 'sent'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Questions resent successfully!'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Factor not found for this assessment.'
        ], 404);
    }

    public function updateTrackingStatus(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|exists:question_tracking,id',
            'status' => 'required|in:sent,pending,completed,declined'
        ]);

        $tracking = QuestionTracking::find($request->tracking_id);
        $tracking->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!'
        ]);
    }

    public function getTrackingStats()
    {
        $stats = QuestionTracking::getStatusCounts();
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function getQuestionsByFactor($id)
    {
        try {
            $factor = Factor::findOrFail($id);
            $questions = Question::where('factor_id', $factor->id)
                                ->where('is_active', true)
                                ->orderBy('order')
                                ->orderBy('created_at')
                                ->get();

            return response()->json($questions);
        } catch (\Exception $e) {
            Log::error('Error in QuestionController@getQuestionsByFactor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading questions'
            ], 500);
        }
    }
}