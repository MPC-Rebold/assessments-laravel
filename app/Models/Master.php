<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Master extends Model
{
    protected $fillable = [
        'title',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function status(): HasOne
    {
        return $this->hasOne(Status::class);
    }

    public function statusString(): string
    {
        if (! $this->status) {
            return '';
        }
        if (! $this->status->has_seed) {
            return 'NoSeed';
        }
        if ($this->courses->isEmpty()) {
            return 'Disconnected';
        }

        $hasMissingCourses = $this->status->missing_courses->isNotEmpty();
        $hasMissingAssessments = $this->status->missing_assessments->isNotEmpty();

        if (! $hasMissingCourses && ! $hasMissingAssessments) {
            return 'Okay';
        } else {
            return 'Warning';
        }
    }

    public function courseForUser(User $user): Course
    {
        return $this->courses()->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();
    }
}
