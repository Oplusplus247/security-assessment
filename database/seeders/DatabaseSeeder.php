<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Factor;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\Response;
use App\Models\CorrectiveAction;
use App\Models\QuestionTracking;
use App\Models\AssessmentForm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create departments first
        $departments = [
            ['name' => 'Overall', 'slug' => 'overall'],
            ['name' => 'IT Infrastructure', 'slug' => 'it-infrastructure'],
            ['name' => 'IR Plan', 'slug' => 'it-plan'],
            ['name' => 'IR Team', 'slug' => 'it-team'],
            ['name' => 'Management Support', 'slug' => 'management-support'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create default user with department
        $overallDept = Department::where('slug', 'overall')->first();
        $user = User::create([
            'name' => 'User Name',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'department_id' => $overallDept->id,
            'role' => 'admin'
        ]);

        // Create factors (the assessment categories)
        $factors = [
            [
                'name' => 'IR Team',
                'slug' => 'ir-team',
                'color_class' => 'bg-green-500',
                'is_active' => true
            ],
            [
                'name' => 'Security Culture',
                'slug' => 'security-culture',
                'color_class' => 'bg-green-400',
                'is_active' => true
            ],
            [
                'name' => 'Collaboration with External Organization',
                'slug' => 'collaboration-external',
                'color_class' => 'bg-green-400',
                'is_active' => true
            ],
            [
                'name' => 'Resource Allocation',
                'slug' => 'resource-allocation',
                'color_class' => 'bg-yellow-400',
                'is_active' => true
            ],
            [
                'name' => 'Communication',
                'slug' => 'communication',
                'color_class' => 'bg-yellow-500',
                'is_active' => true
            ],
            [
                'name' => 'Management Support',
                'slug' => 'management-support',
                'color_class' => 'bg-yellow-400',
                'is_active' => true
            ],
            [
                'name' => 'IT Infrastructure',
                'slug' => 'it-infrastructure',
                'color_class' => 'bg-red-500',
                'is_active' => true
            ],
            [
                'name' => 'IR Plan',
                'slug' => 'ir-plan',
                'color_class' => 'bg-red-400',
                'is_active' => true
            ],
            [
                'name' => 'Standards and Regulatory Compliance',
                'slug' => 'standards-compliance',
                'color_class' => 'bg-green-400',
                'is_active' => true
            ],
            [
                'name' => 'Third-Party Relationship',
                'slug' => 'third-party-relationship',
                'color_class' => 'bg-yellow-400',
                'is_active' => true
            ],
            [
                'name' => 'Training and Awareness',
                'slug' => 'training-awareness',
                'color_class' => 'bg-red-500',
                'is_active' => true
            ]
        ];

        foreach ($factors as $factor) {
            Factor::create($factor);
        }

        // Create questions linked to factors
        $questionsData = [
            // IT Infrastructure Factor Questions
            [
                'factor_slug' => 'it-infrastructure',
                'questions' => [
                    'Our IT infrastructure is adequate to respond to security incidents',
                    'Our organization effectively uses IT and security tools for detection and response to security incidents (e.g. IDS, IPS, SIEM)',
                    'Our IT infrastructure provides the necessary scalability to accommodate an increased load during a security incident without significant degradation',
                    'Our IT infrastructure is updated regularly to prevent adversaries from exploiting system vulnerabilities',
                    'Our cyber playbooks are sufficiently automated for incident response'
                ]
            ],
            // IR Team Factor Questions
            [
                'factor_slug' => 'ir-team',
                'questions' => [
                    'Our organization has a dedicated incident response team',
                    'IR team members have clearly defined roles and responsibilities',
                    'Our IR team receives regular training and certifications',
                    'The IR team has sufficient authority to make critical decisions during incidents'
                ]
            ],
            // Security Culture Factor Questions
            [
                'factor_slug' => 'security-culture',
                'questions' => [
                    'Employees understand their role in cybersecurity',
                    'Security awareness is integrated into company culture',
                    'Staff regularly report security concerns and incidents',
                    'Leadership demonstrates commitment to cybersecurity'
                ]
            ],
            // Communication Factor Questions
            [
                'factor_slug' => 'communication',
                'questions' => [
                    'Clear communication channels exist for incident reporting',
                    'Stakeholders are informed promptly during security incidents',
                    'Communication protocols are tested and updated regularly'
                ]
            ]
        ];

        foreach ($questionsData as $factorQuestions) {
            $factor = Factor::where('slug', $factorQuestions['factor_slug'])->first();
            
            foreach ($factorQuestions['questions'] as $questionText) {
                Question::create([
                    'factor_id' => $factor->id,
                    'question' => $questionText,
                    'is_active' => true
                ]);
            }
        }

        // Create historical assessments with responses
        $dates = [
            '2024-10-04',
            '2024-11-04', 
            '2024-12-04',
            '2025-01-04',
            '2025-02-04',
            '2025-03-04'
        ];

        // Create assessments for each department and date
        foreach (Department::all() as $department) {
            foreach ($dates as $dateString) {
                // First, calculate the readiness level by pre-generating scores
                $questions = Question::all();
                $totalScore = 0;
                $responseCount = 0;
                $responses = [];

                foreach ($questions as $question) {
                    // Generate realistic scores based on factor and department
                    $score = $this->generateRealisticScore($question->factor->slug, $department->slug, $dateString);
                    $responses[] = [
                        'question_id' => $question->id,
                        'score' => $score
                    ];
                    $totalScore += $score;
                    $responseCount++;
                }

                // Calculate readiness level
                $readinessLevel = $responseCount > 0 ? round($totalScore / $responseCount, 1) : 0;

                // Create assessment with calculated readiness level
                $assessment = Assessment::create([
                    'department_id' => $department->id,
                    'user_id' => $user->id,
                    'assessment_date' => Carbon::parse($dateString),
                    'status' => 'completed',
                    'target_level' => 4.0,
                    'readiness_level' => $readinessLevel,
                    'total_score' => $readinessLevel
                ]);

                // Now create the response records
                foreach ($responses as $responseData) {
                    Response::create([
                        'assessment_id' => $assessment->id,
                        'question_id' => $responseData['question_id'],
                        'user_id' => $user->id,
                        'score' => $responseData['score']
                    ]);
                }
            }
        }

        // Create corrective actions
        $itInfrastructureFactor = Factor::where('slug', 'it-infrastructure')->first();
        $infraQuestions = Question::where('factor_id', $itInfrastructureFactor->id)->get();

        if ($infraQuestions->count() >= 2) {
            $actions = [
                [
                    'question_id' => $infraQuestions[0]->id,
                    'action' => 'Install IDS/IPS system for better threat detection',
                    'department' => 'IT Department'
                ],
                [
                    'question_id' => $infraQuestions[1]->id,
                    'action' => 'Update security configurations and implement SIEM solution',
                    'department' => 'IT Department'
                ],
                [
                    'question_id' => $infraQuestions[3]->id ?? $infraQuestions[0]->id,
                    'action' => 'Establish regular patch management schedule',
                    'department' => 'IT Department'
                ]
            ];

            foreach ($actions as $action) {
                CorrectiveAction::create($action);
            }
        }

        // Create question tracking data with more realistic data
        $emails = [
            'staff1@company.com',
            'staff2@company.com', 
            'manager1@company.com',
            'admin@company.com'
        ];

        $assessmentTypes = [
            'IT Infrastructure Assessment',
            'Security Culture Assessment', 
            'IR Plan Assessment',
            'Communication Assessment'
        ];

        $statuses = ['sent', 'pending', 'completed'];

        for ($i = 0; $i < 15; $i++) {
            QuestionTracking::create([
                'date' => now()->subDays(rand(1, 30)),
                'assessment_type' => $assessmentTypes[array_rand($assessmentTypes)],
                'email' => $emails[array_rand($emails)],
                'status' => $statuses[array_rand($statuses)]
            ]);
        }

        // Create assessment form
        AssessmentForm::create([
            'title' => 'Cybersecurity Readiness Assessment',
            'questions' => Question::pluck('question')->toArray(),
            'status' => 'active'
        ]);
    }

    /**
     * Generate realistic scores based on factor type, department, and date progression
     */
    private function generateRealisticScore($factorSlug, $departmentSlug, $dateString)
    {
        $baseScores = [
            'it-infrastructure' => [
                'overall' => 3.5,
                'it-infrastructure' => 2.8,
                'it-plan' => 3.0,
                'it-team' => 3.2,
                'management-support' => 3.8
            ],
            'ir-team' => [
                'overall' => 4.0,
                'it-infrastructure' => 3.5,
                'it-plan' => 4.2,
                'it-team' => 4.5,
                'management-support' => 3.8
            ],
            'security-culture' => [
                'overall' => 3.8,
                'it-infrastructure' => 3.2,
                'it-plan' => 3.5,
                'it-team' => 4.0,
                'management-support' => 4.2
            ],
            'communication' => [
                'overall' => 3.6,
                'it-infrastructure' => 3.0,
                'it-plan' => 3.8,
                'it-team' => 3.9,
                'management-support' => 4.0
            ]
        ];

        // Default scores for other factors
        $defaultScores = [
            'overall' => 3.5,
            'it-infrastructure' => 3.0,
            'it-plan' => 3.2,
            'it-team' => 3.6,
            'management-support' => 3.8
        ];

        $baseScore = $baseScores[$factorSlug][$departmentSlug] ?? $defaultScores[$departmentSlug] ?? 3.0;
        
        // Add some progression over time (slight improvement in more recent dates)
        $dateProgress = Carbon::parse($dateString)->diffInMonths('2024-10-01') * 0.1;
        
        // Add some randomness
        $randomVariation = (rand(-20, 20) / 100); // ±0.2 variation
        
        $finalScore = $baseScore + $dateProgress + $randomVariation;
        
        // Ensure score is between 1 and 5
        return max(1, min(5, round($finalScore, 0)));
    }
}