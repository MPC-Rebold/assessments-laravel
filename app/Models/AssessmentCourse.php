<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentCourse extends Model
{
    protected $fillable = [
        'assessment_id',
        'course_id',
        'assessment_canvas_id',
        'due_at',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function gradeForUser(User $user): array
    {
        $points = QuestionUser::where([
            'user_id' => $user->id,
            'assessment_id' => $this->assessment->id,
            'course_id' => $this->course->id,
            'is_correct' => true,
        ])->count();

        return [
            'points' => $points,
            'max_points' => $this->assessment->questionCount,
        ];
    }
}
