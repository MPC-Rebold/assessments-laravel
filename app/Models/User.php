<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'is_admin',
        'provider',
        'provider_id',
        'provider_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'provider_token',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    public function courses(): BelongsToMany
    {
        // master_id is null if the course is not connected to a master course
        return $this->belongsToMany(Course::class)->whereNotNull('master_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class)->withPivot('answer', 'is_correct');
    }

    public function connectCourses(): void
    {
        if ($this->is_admin) {
            $courses = Course::all();
        } else {
            $courses = Course::whereJsonContains('valid_students', $this->email)->get();
        }
        $this->courses()->sync($courses);
    }

    public function isEnrolled(int $courseId): bool
    {
        return $this->courses->contains($courseId);
    }

    public function assessments(int $courseId = -1): array
    {
        if ($courseId !== -1) {
            $assessments = $this->courses->find($courseId)->master->assessments
                ->sortBy('due_at')->values()->all();

        } else {
            $assessments = $this->courses->map(function ($course) {
                if ($course->master) {
                    return $course->master->map->assessments->flatten();
                }

                return null;
            })->filter()->flatten()->sortBy('due_at')->values()->all();
        }

        return $assessments;
    }
}
