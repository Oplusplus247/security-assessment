<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Factor;
use App\Models\Question;

class QuestionsWithWeightSeeder extends Seeder
{
    public function run()
    {
        $questionsData = [
            'ir-plan' => [
                [
                    'question' => 'Our Organization Has A Comprehensive Incident Response (IR) Plan',
                    'weight' => 3,
                    'description' => 'Critical foundation question for IR planning capability'
                ],
                [
                    'question' => 'Responsibilities And Technical Procedures For Handling Security Incidents Are Clearly Defined In The IR Plan',
                    'weight' => 2,
                    'description' => 'Important for clarity and execution'
                ],
                [
                    'question' => 'Key Stakeholders In The Organization Are Aware Of The IR Plan And Their Roles Within It',
                    'weight' => 2,
                    'description' => 'Critical for effective implementation'
                ],
                [
                    'question' => 'Our IR Plan Is Regularly Reviewed And Tested',
                    'weight' => 3,
                    'description' => 'Essential for maintaining plan effectiveness'
                ],
                [
                    'question' => 'Our Organization Conducts Regular Training And Drills To Ensure IR Plan Effectiveness',
                    'weight' => 2,
                    'description' => 'Important for team preparedness'
                ],
            ],
            'it-infrastructure' => [
                [
                    'question' => 'Our IT infrastructure is adequate to respond to security incidents',
                    'weight' => 3,
                    'description' => 'Fundamental infrastructure capability'
                ],
                [
                    'question' => 'Our organization effectively uses IT and security tools for detection and response to security incidents (e.g. IDS, IPS, SIEM)',
                    'weight' => 4,
                    'description' => 'Critical for automated detection and response'
                ],
                [
                    'question' => 'Our IT infrastructure provides the necessary scalability to accommodate an increased load during a security incident without significant degradation',
                    'weight' => 2,
                    'description' => 'Important for maintaining operations during incidents'
                ],
                [
                    'question' => 'Our IT infrastructure is updated regularly to prevent adversaries from exploiting system vulnerabilities',
                    'weight' => 4,
                    'description' => 'Critical for preventing security incidents'
                ],
                [
                    'question' => 'Our cyber playbooks are sufficiently automated for incident response',
                    'weight' => 3,
                    'description' => 'Important for rapid response'
                ],
            ],
            'ir-team' => [
                [
                    'question' => 'Our organization has a dedicated incident response team',
                    'weight' => 4,
                    'description' => 'Critical for effective incident response'
                ],
                [
                    'question' => 'IR team members have clearly defined roles and responsibilities',
                    'weight' => 3,
                    'description' => 'Essential for organized response'
                ],
                [
                    'question' => 'Our IR team receives regular training and certifications',
                    'weight' => 3,
                    'description' => 'Important for maintaining competency'
                ],
                [
                    'question' => 'The IR team has sufficient authority to make critical decisions during incidents',
                    'weight' => 4,
                    'description' => 'Critical for rapid decision making'
                ],
                [
                    'question' => 'IR team members are available 24/7 for incident response',
                    'weight' => 3,
                    'description' => 'Important for continuous coverage'
                ],
            ],
            'security-culture' => [
                [
                    'question' => 'Employees understand their role in cybersecurity',
                    'weight' => 3,
                    'description' => 'Fundamental for security culture'
                ],
                [
                    'question' => 'Security awareness is integrated into company culture',
                    'weight' => 4,
                    'description' => 'Critical for organizational security'
                ],
                [
                    'question' => 'Staff regularly report security concerns and incidents',
                    'weight' => 3,
                    'description' => 'Important for early detection'
                ],
                [
                    'question' => 'Leadership demonstrates commitment to cybersecurity',
                    'weight' => 4,
                    'description' => 'Critical for setting organizational tone'
                ],
                [
                    'question' => 'Security policies are regularly communicated and updated',
                    'weight' => 2,
                    'description' => 'Important for maintaining awareness'
                ],
            ],
            'communication' => [
                [
                    'question' => 'Clear communication channels exist for incident reporting',
                    'weight' => 4,
                    'description' => 'Critical for incident notification'
                ],
                [
                    'question' => 'Stakeholders are informed promptly during security incidents',
                    'weight' => 3,
                    'description' => 'Important for maintaining trust'
                ],
                [
                    'question' => 'Communication protocols are tested and updated regularly',
                    'weight' => 2,
                    'description' => 'Important for protocol effectiveness'
                ],
                [
                    'question' => 'External communication with customers and partners is well-managed during incidents',
                    'weight' => 3,
                    'description' => 'Important for reputation management'
                ],
            ],
            'management-support' => [
                [
                    'question' => 'Senior management provides adequate resources for cybersecurity',
                    'weight' => 4,
                    'description' => 'Critical for program success'
                ],
                [
                    'question' => 'Cybersecurity is regularly discussed at board/executive level',
                    'weight' => 3,
                    'description' => 'Important for strategic alignment'
                ],
                [
                    'question' => 'Management supports incident response activities and decisions',
                    'weight' => 4,
                    'description' => 'Critical for effective response'
                ],
                [
                    'question' => 'There is clear accountability for cybersecurity at the executive level',
                    'weight' => 3,
                    'description' => 'Important for governance'
                ],
            ],
        ];

        // Create questions for each factor
        foreach ($questionsData as $factorSlug => $questions) {
            $factor = Factor::where('slug', $factorSlug)->first();
            
            if ($factor) {
                foreach ($questions as $index => $questionData) {
                    Question::create([
                        'factor_id' => $factor->id,
                        'question' => $questionData['question'],
                        'description' => $questionData['description'],
                        'weight' => $questionData['weight'],
                        'order' => $index + 1,
                        'is_active' => true
                    ]);
                }
            }
        }

        $this->command->info('Questions with weights seeded successfully!');
    }
}