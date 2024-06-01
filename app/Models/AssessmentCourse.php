<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'course_id',
        'assessment_canvas_id',
        'due_at',
        'is_active',
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
     * Returns either the points for a user
     * or complete/incomplete for specification grading
     *
     * @param User $user the user to get the grade for
     * @return int|string the points or percentage for a user
     */
    public function gradeForUser(User $user): int|string
    {
        $is_specification = $this->course->specification_grading;
        $threshold = $this->course->specification_grading_threshold;

        if ($is_specification) {
            return $this->percentageForUser($user) >= $threshold ? 'complete' : 'incomplete';
        } else {
            return $this->pointsForUser($user);
        }
    }

    /**
     * Returns the grade in points for a user
     *
     * @param User $user the user to get the points for
     * @return int the points for a user
     */
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

    /**
     * Returns the grade in percentage for a user
     *
     * @param User $user the user to get the percentage for
     * @return float the percentage for a user
     */
    public function percentageForUser(User $user): float
    {
        $totalQuestions = $this->assessment->questions->count();
        $points = $this->pointsForUser($user);

        return $totalQuestions > 0 ? round($points / $totalQuestions, 3) : 0;
    }

    /**
     * Returns the average grade for the assessment in percentage
     * for all users in the course
     *
     * @return float the average grade for the assessment
     *
     * @noinspection PhpUnused
     */
    public function getAverageGrade(): float
    {
        $totalQuestions = $this->assessment->questions->count() * count($this->course->valid_students);

        $totalPoints = QuestionUser::where([
            'course_id' => $this->course->id,
            'is_correct' => true,
        ])->whereHas('question', function ($query) {
            $query->where('assessment_id', $this->assessment->id);
        })->count();

        return $totalQuestions > 0 ? ($totalPoints / $totalQuestions) : 0;
    }

    /**
     * Returns whether the assessment is past due
     *
     * @return bool true if due date is in the past and not null
     */
    public function isPastDue(): bool
    {
        if ($this->due_at === null) {
            return false;
        }

        return Carbon::parse($this->due_at)->isPast();
    }
}
