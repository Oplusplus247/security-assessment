<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Factor;

class FactorSeeder extends Seeder
{
    public function run()
    {
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
    }
}