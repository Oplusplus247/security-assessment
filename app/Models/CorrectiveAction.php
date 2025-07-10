<?php
// app/Models/CorrectiveAction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CorrectiveAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'action',
        'department'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Add scopes for filtering
    public function scopeByDepartment($query, $departmentName)
    {
        return $query->where(function($q) use ($departmentName) {
            $q->where('department', $departmentName)
              ->orWhere('department', 'All Departments')
              ->orWhereNull('department');
        });
    }

    public function scopeByFactor($query, $factorId)
    {
        return $query->whereHas('question', function($q) use ($factorId) {
            $q->where('factor_id', $factorId);
        });
    }

    // Get formatted department name
    public function getFormattedDepartmentAttribute()
    {
        return $this->department ?: 'All Departments';
    }
}