<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color_class',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function responses()
    {
        return $this->hasManyThrough(Response::class, Question::class);
    }

    public function getReadinessLevel($departmentId = null)
    {
        $query = $this->responses()->latest();
        
        if ($departmentId) {
            $query->whereHas('assessment', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $responses = $query->get();
        
        if ($responses->isEmpty()) {
            return 0;
        }

        return round($responses->avg('score'), 1);
    }

    public function getColorClass()
    {
        $level = $this->getReadinessLevel();
        
        if ($level >= 4.0) return 'bg-green-500';
        if ($level >= 3.0) return 'bg-yellow-400';
        if ($level >= 2.0) return 'bg-yellow-500';
        return 'bg-red-500';
    }
}