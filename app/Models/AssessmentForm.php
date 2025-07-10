<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssessmentForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'questions',
        'status'
    ];

    protected $casts = [
        'questions' => 'array'
    ];
}
