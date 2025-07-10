<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Factor;

class UpdateQuestionsWeightSeeder extends Seeder
{
    public function run()
    {
        $factorMappings = [
            'it-infrastructure' => [
                'weight' => 4,
                'keywords' => ['IT infrastructure', 'security tools', 'scalability', 'updated regularly', 'automated']
            ],
            'ir-plan' => [
                'weight' => 3,
                'keywords' => ['IR plan', 'incident response', 'comprehensive', 'procedures', 'stakeholders', 'reviewed', 'tested']
            ],
            'security-culture' => [
                'weight' => 3,
                'keywords' => ['employees', 'awareness', 'culture', 'staff', 'leadership', 'commitment']
            ],
            'communication' => [
                'weight' => 2,
                'keywords' => ['communication', 'channels', 'informed', 'protocols', 'external communication']
            ],
            'management-support' => [
                'weight' => 4,
                'keywords' => ['management', 'resources', 'board', 'executive', 'supports', 'accountability']
            ]
        ];

        // Get all questions without factor assignment
        $questionsWithoutFactor = Question::whereNull('factor_id')->get();

        foreach ($questionsWithoutFactor as $question) {
            $assignedFactor = null;
            $assignedWeight = 1;

            // Try to match question to factor based on keywords
            foreach ($factorMappings as $factorSlug => $config) {
                $keywords = $config['keywords'];
                $questionText = strtolower($question->question);

                foreach ($keywords as $keyword) {
                    if (strpos($questionText, strtolower($keyword)) !== false) {
                        $factor = Factor::where('slug', $factorSlug)->first();
                        if ($factor) {
                            $assignedFactor = $factor->id;
                            $assignedWeight = $config['weight'];
                            break 2; // Break out of both loops
                        }
                    }
                }
            }

            // If no specific factor found, assign to IT Infrastructure as default
            if (!$assignedFactor) {
                $itInfrastructure = Factor::where('slug', 'it-infrastructure')->first();
                if ($itInfrastructure) {
                    $assignedFactor = $itInfrastructure->id;
                    $assignedWeight = 2; // Default weight
                }
            }

            // Update the question
            if ($assignedFactor) {
                $question->update([
                    'factor_id' => $assignedFactor,
                    'weight' => $assignedWeight
                ]);

                $this->command->info("Updated question ID {$question->id}: assigned to factor {$assignedFactor} with weight {$assignedWeight}");
            }
        }

        // Update questions that already have factor_id but no weight
        $questionsWithoutWeight = Question::whereNotNull('factor_id')
                                         ->where(function($query) {
                                             $query->whereNull('weight')
                                                   ->orWhere('weight', 0);
                                         })
                                         ->get();

        foreach ($questionsWithoutWeight as $question) {
            $factor = $question->factor;
            if ($factor) {
                $weight = $factorMappings[$factor->slug]['weight'] ?? 2;
                $question->update(['weight' => $weight]);
                $this->command->info("Updated weight for question ID {$question->id} to {$weight}");
            }
        }

        $this->command->info('Questions weight update completed!');
    }
}