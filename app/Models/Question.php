<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'assessment_id',
        'question',
        'answer',
        'max_attempts',
        'number',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('answer', 'is_correct');
    }

    public function getGuessesLeft(User $user, Course $course): int
    {
        $guesses = QuestionUser::where(
            [
                'user_id' => $user->id,
                'question_id' => $this->id,
                'course_id' => $course->id,
            ]
        )->count();

        return $this->max_attempts - $guesses;
    }

    public function isCorrect(User $user, Course $course): bool
    {
        return QuestionUser::where(
            [
                'user_id' => $user->id,
                'question_id' => $this->id,
                'course_id' => $course->id,
                'is_correct' => true,
            ]
        )->exists();
    }
}
