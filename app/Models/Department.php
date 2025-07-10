<?php
// app/Models/Department.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function getLatestAssessment()
    {
        return $this->assessments()
                    ->latest('assessment_date')
                    ->first();
    }

    public function getCurrentReadinessLevel()
    {
        $latest = $this->getLatestAssessment();
        return $latest ? $latest->readiness_level : 0;
    }

    public function getTargetLevel()
    {
        $latest = $this->getLatestAssessment();
        return $latest ? $latest->target_level : 5.0;
    }

    public function getReadinessProgress()
    {
        $current = $this->getCurrentReadinessLevel();
        $target = $this->getTargetLevel();
        
        if ($target == 0) return 0;
        
        return ($current / $target) * 100;
    }
}