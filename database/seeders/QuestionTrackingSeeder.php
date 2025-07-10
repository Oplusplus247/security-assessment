<?php
// database/seeders/QuestionTrackingSeeder.php
// Run: php artisan make:seeder QuestionTrackingSeeder

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionTracking;
use Carbon\Carbon;

class QuestionTrackingSeeder extends Seeder
{
    public function run()
    {
        // Clear existing tracking data
        QuestionTracking::truncate();

        // Sample email addresses
        $emails = [
            'staff1@company.com',
            'staff2@company.com', 
            'staff3@company.com',
            'staff4@company.com',
            'staff5@company.com',
            'manager1@company.com',
            'manager2@company.com',
            'admin1@company.com',
            'admin2@company.com',
            'director@company.com',
            'john.doe@company.com',
            'jane.smith@company.com',
            'mike.wilson@company.com',
            'sarah.johnson@company.com',
            'david.brown@company.com',
        ];

        // Assessment types (factors)
        $assessmentTypes = [
            'IT Infrastructure Assessment',
            'Security Culture Assessment', 
            'IR Plan Assessment',
            'Communication Assessment',
            'Management Support Assessment',
            'Training and Awareness Assessment',
            'Overall Readiness Assessment'
        ];

        // Status options
        $statuses = ['sent', 'pending', 'completed', 'declined'];
        $statusWeights = [30, 40, 25, 5]; // Weighted distribution

        // Create tracking records for the last 30 days
        for ($i = 0; $i < 50; $i++) {
            // Random date within last 30 days
            $date = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            // Weighted random status selection
            $statusIndex = $this->getWeightedRandomIndex($statusWeights);
            $status = $statuses[$statusIndex];
            
            QuestionTracking::create([
                'date' => $date,
                'assessment_type' => $assessmentTypes[array_rand($assessmentTypes)],
                'email' => $emails[array_rand($emails)],
                'status' => $status
            ]);
        }

        // Create some recent tracking entries for today
        for ($i = 0; $i < 5; $i++) {
            QuestionTracking::create([
                'date' => Carbon::today()->addHours(rand(8, 17))->addMinutes(rand(0, 59)),
                'assessment_type' => $assessmentTypes[array_rand($assessmentTypes)],
                'email' => $emails[array_rand($emails)],
                'status' => $statuses[array_rand($statuses)]
            ]);
        }

        $this->command->info('Question tracking data seeded successfully!');
        $this->command->info('Created ' . QuestionTracking::count() . ' tracking records.');
    }

    /**
     * Get weighted random index
     */
    private function getWeightedRandomIndex($weights)
    {
        $totalWeight = array_sum($weights);
        $randomNumber = rand(1, $totalWeight);
        
        $weightSum = 0;
        foreach ($weights as $index => $weight) {
            $weightSum += $weight;
            if ($randomNumber <= $weightSum) {
                return $index;
            }
        }
        
        return 0; // Fallback
    }
}