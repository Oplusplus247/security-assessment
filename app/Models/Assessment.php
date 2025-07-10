<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'user_id',
        'assessment_date',
        'status',
        'total_score',
        'readiness_level',
        'target_level'
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'readiness_level' => 'decimal:1',
        'target_level' => 'decimal:1',
        'total_score' => 'decimal:1'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    // Calculate readiness level from responses
    public function calculateReadinessLevel()
    {
        $responses = $this->responses;
        
        if ($responses->isEmpty()) {
            return 0;
        }

        $averageScore = $responses->avg('score');
        $this->readiness_level = round($averageScore, 1);
        $this->total_score = $averageScore;
        $this->save();

        return $this->readiness_level;
    }

    // Get readiness percentage for progress bars
    public function getReadinessPercentage()
    {
        if ($this->target_level == 0) return 0;
        return ($this->readiness_level / $this->target_level) * 100;
    }
}

