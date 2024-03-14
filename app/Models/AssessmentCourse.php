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

    /***
     * Returns either the points for the user
     * or the percentage if specification grading is on
     *
     * @param User $user
     * @return int|float the points or percentage for the user
     */
    public function gradeForUser(User $user): int|float
    {
        if ($this->course->specification_grading) {
            return $this->percentageForUser($user);
        } else {
            return $this->pointsForUser($user);
        }
    }

    public function pointsForUser(User $user): int
    {
        return QuestionUser::where([
            'user_id' => $user->id,
            'course_id' => $this->course->id,
            'is_correct' => true,
        ])->whereHas('question', function ($query) {
            $query->where('assessment_id', $this->assessment->id);
        })->count();
    }

    public function percentageForUser(User $user): float
    {
        $totalQuestions = $this->assessment->questions->count();
        $points = $this->pointsForUser($user);

        return $totalQuestions > 0 ? round($points / $totalQuestions, 3) : 0;
    }

    public function getAverageGrade(): float
    {
        $totalQuestions = $this->assessment->questions->count() * $this->course->users->count();

        $totalPoints = QuestionUser::where([
            'course_id' => $this->course->id,
            'is_correct' => true,
        ])->whereHas('question', function ($query) {
            $query->where('assessment_id', $this->assessment->id);
        })->count();

        return $totalQuestions > 0 ? ($totalPoints / $totalQuestions) : 0;
    }
}
