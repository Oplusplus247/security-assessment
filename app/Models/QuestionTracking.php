<?php
// app/Models/QuestionTracking.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionTracking extends Model
{
    use HasFactory;

    protected $table = 'question_tracking';

    protected $fillable = [
        'date',
        'assessment_type',
        'email',
        'status'
    ];

    protected $casts = [
        'date' => 'datetime' // Changed from 'date' to 'datetime' for better handling
    ];

    // Scopes for filtering
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    // Accessors
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'sent' => 'bg-blue-100 text-blue-800',
            'pending' => 'bg-yellow-100 text-yellow-800', 
            'completed' => 'bg-green-100 text-green-800',
            'declined' => 'bg-red-100 text-red-800'
        ];
        
        return $classes[strtolower($this->status)] ?? 'bg-gray-100 text-gray-800';
    }

    // Get status counts
    public static function getStatusCounts()
    {
        return [
            'sent' => self::byStatus('sent')->count(),
            'pending' => self::byStatus('pending')->count(),
            'completed' => self::byStatus('completed')->count(),
            'declined' => self::byStatus('declined')->count(),
        ];
    }

    // Get recent activity
    public static function getRecentActivity($days = 7)
    {
        return self::recent($days)->orderBy('date', 'desc')->limit(10)->get();
    }
}