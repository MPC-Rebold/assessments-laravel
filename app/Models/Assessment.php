<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'title',
        'due_at',
        'master_id',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'assessment_courses');
    }

    public function assessmentCourses(): HasMany
    {
        return $this->hasMany(AssessmentCourse::class);
    }

    public function questionCount(): int
    {
        return $this->questions()->count();
    }
}
