<?php

if (!function_exists('format_readiness_level')) {
    function format_readiness_level($level)
    {
        return number_format($level, 1);
    }
}

if (!function_exists('get_readiness_color')) {
    function get_readiness_color($level, $target = 5)
    {
        $percentage = ($level / $target) * 100;
        
        if ($percentage >= 80) return 'text-green-600';
        if ($percentage >= 60) return 'text-yellow-600';
        return 'text-red-600';
    }
}

if (!function_exists('get_status_badge_class')) {
    function get_status_badge_class($status)
    {
        return match($status) {
            'completed' => 'bg-green-100 text-green-800',
            'sent' => 'bg-blue-100 text-blue-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}

if (!function_exists('department_color')) {
    function department_color($slug)
    {
        return match($slug) {
            'overall' => '#3B82F6',
            'it-infrastructure' => '#8B5CF6',
            'it-plan' => '#06B6D4',
            'it-team' => '#3ec516',
            'management-support' => '#F59E0B',
            default => '#6B7280'
        };
    }
}

if (!function_exists('getReadinessStage')) {
    /**
     * Get readiness stage based on score
     * 
     * @param float $score
     * @return string
     */
    function getReadinessStage($score)
    {
        if ($score >= 0.00 && $score <= 1.25) {
            return 'Beginner';
        } elseif ($score >= 1.26 && $score <= 2.50) {
            return 'Formative';
        } elseif ($score >= 2.51 && $score <= 3.75) {
            return 'Progressive';
        } elseif ($score >= 3.76 && $score <= 5.00) {
            return 'Mature';
        } else {
            return 'Unknown';
        }
    }
}

if (!function_exists('getReadinessStageColor')) {
    /**
     * Get color class for readiness stage
     * 
     * @param string $stage
     * @return string
     */
    function getReadinessStageColor($stage)
    {
        switch ($stage) {
            case 'Beginner':
                return 'text-red-600';
            case 'Formative':
                return 'text-orange-600';
            case 'Progressive':
                return 'text-blue-600';
            case 'Mature':
                return 'text-green-600';
            default:
                return 'text-gray-600';
        }
    }
}