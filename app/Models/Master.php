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

    public function statusStrings(): array
    {
        $statusStrings = [];

        if (! $this->status) {
            return [];
        }
        if (! $this->status->has_seed) {
            $statusStrings[] = 'NoSeed';
        }

        if ($this->courses->isEmpty()) {
            $statusStrings[] = 'Disconnected';
        }

        $hasMissingCourses = $this->status->missing_courses->isNotEmpty();
        $hasMissingAssessments = $this->status->missing_assessments->isNotEmpty();

        if ($hasMissingCourses or $hasMissingAssessments) {
            $statusStrings[] = 'Warning';
        }

        if (!$statusStrings) {
            $statusStrings[] = 'Okay';
        }

        return $statusStrings;
    }

    public function courseForUser(User $user): Course
    {
        return $this->courses()->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();
    }
}
