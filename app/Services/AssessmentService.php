<?php 

namespace App\Services;

use App\Models\Assessment;
use App\Models\Department;
use App\Models\Question;
use Illuminate\Support\Collection;

class AssessmentService
{
    public function calculateOverallReadiness(): float
    {
        $departments = Department::where('slug', '!=', 'overall')->get();
        $totalReadiness = 0;
        $count = 0;
        
        foreach ($departments as $department) {
            $latestAssessment = $department->getLatestAssessment();
            if ($latestAssessment) {
                $totalReadiness += $latestAssessment->readiness_level;
                $count++;
            }
        }
        
        return $count > 0 ? round($totalReadiness / $count, 1) : 0;
    }
    
    public function getReadinessByFactor(): array
    {
        $factors = [
            'security-policies' => ['name' => 'Security Policies', 'color' => '#3ec516'],
            'cybersecurity-external' => ['name' => 'Cybersecurity with External Partners', 'color' => '#22C55E'],
            'asset-management' => ['name' => 'Asset Management', 'color' => '#F59E0B'],
            'network-protection' => ['name' => 'Network Protection', 'color' => '#f34b26'],
            'systems-registry' => ['name' => 'Systems and Registry Management', 'color' => '#22C55E'],
            'third-party' => ['name' => 'Third-Party Interfaces', 'color' => '#FDE047'],
            'training-awareness' => ['name' => 'Training and Awareness', 'color' => '#f34b26']
        ];
        
        // Simulate factor-based readiness levels
        // In a real application, this would be calculated from actual assessment data
        foreach ($factors as $key => &$factor) {
            $factor['readiness'] = rand(15, 45) / 10; // Random between 1.5 and 4.5
        }
        
        return $factors;
    }
    
    public function getQuestionProgress(): array
    {
        $questions = Question::where('is_active', true)->get();
        $progress = [];
        
        foreach ($questions as $question) {
            $progress[] = [
                'id' => $question->id,
                'question' => $question->question,
                'current_score' => $question->current_score,
                'percentage' => ($question->current_score / 5) * 100
            ];
        }
        
        return $progress;
    }
}