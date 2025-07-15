<?php

namespace App\Http\Controllers;

use App\Models\CorrectiveAction;
use App\Models\Factor;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CorrectiveActionController extends Controller
{
    public function index()
    {
        try {
            // Get all factors with their corrective actions
            $factors = Factor::where('is_active', true)->get();

            // Load corrective actions for each factor
            foreach ($factors as $factor) {
                $factor->correctiveActions = CorrectiveAction::whereHas('question', function($q) use ($factor) {
                    $q->where('factor_id', $factor->id);
                })->with('question')->get();
                
                $factor->corrective_actions_count = $factor->correctiveActions->count();
            }

            return view('admin.corrective-actions', compact('factors'));
            
        } catch (\Exception $e) {
            Log::error('Error in CorrectiveActionController@index: ' . $e->getMessage());
            return back()->with('error', 'Error loading corrective actions: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'question_id' => 'required|exists:questions,id',
                'action' => 'required|string|max:1000',
                'department' => 'nullable|string|max:255' // Removed the 'in:' restriction to allow free text
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Clean up department input - trim whitespace and set null if empty
            $department = $request->department;
            if (empty(trim($department))) {
                $department = null;
            } else {
                $department = trim($department);
            }

            $correctiveAction = CorrectiveAction::create([
                'question_id' => $request->question_id,
                'action' => $request->action,
                'department' => $department
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Corrective action added successfully!',
                'action' => $correctiveAction->load('question')
            ]);

        } catch (\Exception $e) {
            Log::error('Error in CorrectiveActionController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating corrective action: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $action = CorrectiveAction::with('question.factor')->findOrFail($id);
            return response()->json($action);
        } catch (\Exception $e) {
            Log::error('Error in CorrectiveActionController@show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Corrective action not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $action = CorrectiveAction::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'question_id' => 'required|exists:questions,id',
                'action' => 'required|string|max:1000',
                'department' => 'nullable|string|max:255' // Removed the 'in:' restriction to allow free text
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Clean up department input - trim whitespace and set null if empty
            $department = $request->department;
            if (empty(trim($department))) {
                $department = null;
            } else {
                $department = trim($department);
            }

            $action->update([
                'question_id' => $request->question_id,
                'action' => $request->action,
                'department' => $department
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Corrective action updated successfully!',
                'action' => $action->load('question')
            ]);

        } catch (\Exception $e) {
            Log::error('Error in CorrectiveActionController@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating corrective action: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $action = CorrectiveAction::findOrFail($id);
            $action->delete();

            return response()->json([
                'success' => true,
                'message' => 'Corrective action deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in CorrectiveActionController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting corrective action: ' . $e->getMessage()
            ], 500);
        }
    }
}