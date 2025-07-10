<?php
// app/Http/Controllers/DashboardController.php
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
        
        // Get current overall readiness level
        $currentReadiness = $this->getCurrentReadinessLevel($departmentId);
        
        // Get aggregated readiness by factor for radar chart
        $aggregatedReadiness = $this->getAggregatedReadiness($departmentId);
        
        // Get readiness level by factor for the grid
        $factorReadiness = $this->getFactorReadiness($departmentId);
        
        // Get historical assessment data
        $historicalData = $this->getHistoricalData($departmentId);
        
        // Get departments for dropdown
        $departments = Department::all();
        
        return view('dashboard.index', compact(
            'currentReadiness',
            'aggregatedReadiness', 
            'factorReadiness',
            'historicalData',
            'departments'
        ));
    }

    public function factorDashboard(Request $request)
    {
        $departmentSlug = $request->get('department', 'overall');
        $factorSlug = $request->get('factor', 'it-infrastructure');
        
        // Find the department and factor
        $department = $departmentSlug === 'overall' ? null : Department::where('slug', $departmentSlug)->first();
        $factor = Factor::where('slug', $factorSlug)->first();
        
        if (!$factor) {
            $factor = Factor::where('slug', 'it-infrastructure')->first();
        }
        
        // Get current assessment for this factor
        $currentAssessment = $this->getFactorAssessment($factor->id, $department?->id);
        
        // Get questions for this factor with their current scores
        $questions = $this->getFactorQuestions($factor->id, $department?->id);
        
        // Get historical data for this factor
        $historicalData = $this->getFactorHistoricalData($factor->id, $department?->id);
        
        // Get departments for dropdown
        $departments = Department::all();
        
        return view('dashboard.factor', compact(
            'currentAssessment', 
            'questions', 
            'historicalData', 
            'department',
            'factor',
            'departments'
        ));
    }

    public function historicalAssessment(Request $request)
    {
        $departments = Department::all();
        $selectedDepartment = $request->get('department', 'overall');
        
        $department = $selectedDepartment === 'overall' ? null : Department::where('slug', $selectedDepartment)->first();
        
        // Get historical data for selected department
        $historicalData = $this->getHistoricalData($department?->id);
        
        return view('dashboard.historical', compact(
            'departments', 
            'selectedDepartment', 
            'historicalData'
        ));
    }

    // Helper methods
    private function getCurrentReadinessLevel($departmentId = null)
    {
        $query = Assessment::query();
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $latestAssessment = $query->latest('assessment_date')->first();
        
        if (!$latestAssessment) {
            return (object) [
                'readiness_level' => 0,
                'target_level' => 5.0
            ];
        }
        
        return $latestAssessment;
    }

    private function getAggregatedReadiness($departmentId = null)
    {
        $factors = Factor::where('is_active', true)->get();
        $aggregated = [];
        
        foreach ($factors as $factor) {
            $aggregated[$factor->name] = $factor->getReadinessLevel($departmentId);
        }
        
        return $aggregated;
    }

    private function getFactorReadiness($departmentId = null)
    {
        $factors = Factor::where('is_active', true)->get();
        $factorReadiness = [];
        
        foreach ($factors as $factor) {
            $level = $factor->getReadinessLevel($departmentId);
            $factorReadiness[] = [
                'name' => $factor->name,
                'level' => $level,
                'color' => $this->getColorClass($level),
                'slug' => $factor->slug
            ];
        }
        
        return $factorReadiness;
    }

    private function getHistoricalData($departmentId = null)
    {
        $query = Assessment::query();
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $assessments = $query->orderBy('assessment_date')
                           ->take(12) // Last 12 assessments
                           ->get();
        
        return $assessments->map(function ($assessment) {
            return [
                'date' => $assessment->assessment_date->format('d/m/Y'),
                'readiness_level' => $assessment->readiness_level,
                'target_level' => $assessment->target_level
            ];
        })->toArray();
    }

    private function getFactorAssessment($factorId, $departmentId = null)
    {
        $factor = Factor::find($factorId);
        $readinessLevel = $factor->getReadinessLevel($departmentId);
        
        return (object) [
            'readiness_level' => $readinessLevel,
            'target_level' => 5.0
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
            return (object) [
                'id' => $question->id,
                'question' => $question->question,
                'current_score' => $question->getCurrentScore($departmentId),
                'weight' => $question->weight ?? 1 // FIXED: Added missing weight
            ];
        });
    }

    private function getFactorHistoricalData($factorId, $departmentId = null)
    {
        // Get responses for this factor over time
        $responses = Response::whereHas('question', function ($query) use ($factorId) {
            $query->where('factor_id', $factorId);
        });
        
        if ($departmentId) {
            $responses->whereHas('assessment', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        }
        
        $responses = $responses->with(['assessment'])
                             ->orderBy('created_at')
                             ->get()
                             ->groupBy(function ($response) {
                                 return $response->assessment->assessment_date->format('Y-m');
                             });
        
        $historicalData = [];
        foreach ($responses as $monthKey => $monthResponses) {
            $avgScore = $monthResponses->avg('score');
            $date = Carbon::createFromFormat('Y-m', $monthKey);
            
            $historicalData[] = [
                'date' => $date->format('d/m/Y'),
                'readiness_level' => round($avgScore, 1),
                'target_level' => 4.0 // Default target
            ];
        }
        
        return $historicalData;
    }
    private function getColorClass($level)
    {
        if ($level >= 3.0) {
            return 'green-500';
        } elseif ($level >= 2.0) {
            return 'orange-500';
        } else {
            return 'red-500';
        }
    }

    public function getHistoricalDataAjax(Request $request)
    {
        $departmentSlug = $request->get('department', 'overall');
        $department = $departmentSlug === 'overall' ? null : Department::where('slug', $departmentSlug)->first();
        
        $historicalData = $this->getHistoricalData($department?->id);
        
        return response()->json([
            'success' => true,
            'data' => $historicalData
        ]);
    }

    // FIXED: Updated method to properly handle department filtering
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
        
        // Use the helper method with proper department filtering
        $questions = $this->getFactorQuestions($factor->id, $department?->id);
        
        return response()->json([
            'success' => true,
            'questions' => $questions,
            'factor' => $factor,
            'department' => $department
        ]);
    }

    // FIXED: Updated method to properly handle department filtering
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
        
        // Get corrective actions for this factor
        $correctiveActionsQuery = \App\Models\CorrectiveAction::whereHas('question', function($q) use ($factor) {
            $q->where('factor_id', $factor->id);
        })->with('question');
        
        // FIXED: Apply department filter properly
        if ($department) {
            // Filter by department name or show all if department is 'overall'
            $correctiveActionsQuery->where(function($query) use ($department) {
                $query->where('department', $department->name)
                      ->orWhere('department', 'All Departments')
                      ->orWhereNull('department');
            });
        }
        
        $correctiveActions = $correctiveActionsQuery->get();
        
        // Format the data for frontend
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