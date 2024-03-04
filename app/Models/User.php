<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
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

    public function canvas(): HasOne
    {
        return $this->hasOne(UserCanvas::class, 'user_email', 'email');
    }

    public function connectCourses(): void
    {
        if ($this->is_admin) {
            $courses = Course::all();
        } else {
            //            $courses = Course::whereJsonContains('valid_students', $this->email)->get();
            $courses = Course::all();
            $courses = $courses->filter(function ($course) {
                return in_array($this->email, $course->valid_students);
            });
        }
        $this->courses()->sync($courses);
    }

    public function isEnrolled(int $courseId): bool
    {
        return $this->courses->contains($courseId);
    }

    /**
     * Returns the user's assessments for a given course or all courses sorted by due date.
     *
     * @param int|null $courseId The course id, or null for all courses
     * @return Collection
     */
    public function assessments(?int $courseId = null): Collection
    {
        if ($courseId) {
            return $this->courses->find($courseId)->assessments->sortBy('due_at');
        }

        return $this->courses->map->assessments->sortBy('due_at')->flatten();
    }
}
