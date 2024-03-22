<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'id',
        'title',
        'valid_students',
        'valid_assessments',
        'specification_grading',
        'specification_grading_threshold',
        'marked_for_deletion',
    ];

    protected $casts = [
        'valid_students' => 'array',
        'valid_assessments' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'assessment_courses')
            ->withPivot('due_at', 'assessment_canvas_id');
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    public function assessmentCourses(): HasMany
    {
        return $this->hasMany(AssessmentCourse::class);
    }
}
