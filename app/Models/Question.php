<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'factor_id',
        'question',
        'description',
        'weight',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weight' => 'integer',
        'order' => 'integer'
    ];

    // Relationships
    public function factor()
    {
        return $this->belongsTo(Factor::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    public function correctiveActions()
    {
        return $this->hasMany(CorrectiveAction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByFactor($query, $factorId)
    {
        return $query->where('factor_id', $factorId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    // Methods
    
    /**
     * Get latest score for this question
     */
    public function getCurrentScore($departmentId = null)
    {
        $query = $this->responses()->latest();
        
        if ($departmentId) {
            $query->whereHas('assessment', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $response = $query->first();
        return $response ? $response->score : 0;
    }

    /**
     * Get average score for this question across all responses
     */
    public function getAverageScore($departmentId = null)
    {
        $query = $this->responses();
        
        if ($departmentId) {
            $query->whereHas('assessment', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return round($query->avg('score') ?? 0, 1);
    }

    /**
     * Get weighted score (score * weight)
     */
    public function getWeightedScore($departmentId = null)
    {
        $currentScore = $this->getCurrentScore($departmentId);
        return $currentScore * $this->weight;
    }

    /**
     * Get all responses with their assessments
     */
    public function getResponseHistory($departmentId = null)
    {
        $query = $this->responses()->with(['assessment', 'user']);
        
        if ($departmentId) {
            $query->whereHas('assessment', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Check if question has any responses
     */
    public function hasResponses()
    {
        return $this->responses()->exists();
    }

    /**
     * Get weight multiplier label (X1, X2, etc.)
     */
    public function getWeightLabel()
    {
        return 'X' . $this->weight;
    }

    /**
     * Calculate maximum possible score for this question
     */
    public function getMaxPossibleScore()
    {
        return 5 * $this->weight; // 5 is max score per question
    }

    /**
     * Get performance percentage for this question
     */
    public function getPerformancePercentage($departmentId = null)
    {
        $currentScore = $this->getCurrentScore($departmentId);
        $maxScore = 5; // Maximum score is 5
        
        return $maxScore > 0 ? round(($currentScore / $maxScore) * 100, 1) : 0;
    }

    /**
     * Duplicate this question
     */
    public function duplicate()
    {
        return self::create([
            'factor_id' => $this->factor_id,
            'question' => $this->question . ' (Copy)',
            'description' => $this->description,
            'weight' => $this->weight,
            'order' => $this->order,
            'is_active' => true
        ]);
    }

    /**
     * Soft delete by setting is_active to false
     */
    public function softDelete()
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set order when creating new question
        static::creating(function ($question) {
            if (is_null($question->order)) {
                $maxOrder = static::where('factor_id', $question->factor_id)
                                  ->max('order') ?? 0;
                $question->order = $maxOrder + 1;
            }
        });
    }
}