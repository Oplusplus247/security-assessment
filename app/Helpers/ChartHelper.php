<?php

namespace App\Helpers;

class ChartHelper
{
    public static function generateGaugeData($current, $target = 5)
    {
        $percentage = ($current / $target) * 100;
        
        return [
            'datasets' => [[
                'data' => [$current, $target - $current],
                'backgroundColor' => [
                    $percentage >= 80 ? '#3ec516' : ($percentage >= 60 ? '#F59E0B' : '#f34b26'),
                    '#E5E7EB'
                ],
                'borderWidth' => 0,
                'cutout' => '70%'
            ]]
        ];
    }
    
    public static function generateHistoricalData($assessments)
    {
        $labels = [];
        $readinessData = [];
        $targetData = [];
        
        foreach ($assessments as $assessment) {
            $labels[] = $assessment->assessment_date->format('m/d/Y');
            $readinessData[] = $assessment->readiness_level;
            $targetData[] = $assessment->target_level;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Readiness level',
                    'data' => $readinessData,
                    'backgroundColor' => '#3B82F6',
                    'borderRadius' => 4
                ],
                [
                    'label' => 'Target level',
                    'data' => $targetData,
                    'backgroundColor' => '#93C5FD',
                    'borderRadius' => 4
                ]
            ]
        ];
    }
}