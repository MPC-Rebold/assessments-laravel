<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Master extends Model
{
    final public const string NO_SEED = 'NO_SEED';

    final public const string DISCONNECTED = 'DISCONNECTED';

    final public const string WARNING = 'WARNING';

    final public const string OKAY = 'OKAY';

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

        $hasMissingAssessmentSeeds = $this->status->missing_assessment_seeds->isNotEmpty();

        if (! $this->status->has_seed or $hasMissingAssessmentSeeds) {
            $statusStrings[] = self::NO_SEED;
        }

        if ($this->courses->isEmpty()) {
            $statusStrings[] = self::DISCONNECTED;
        }

        $hasMissingCourses = $this->status->missing_courses->isNotEmpty();
        $hasMissingAssessments = $this->status->missing_assessments->isNotEmpty();

        if ($hasMissingCourses or $hasMissingAssessments) {
            $statusStrings[] = self::WARNING;
        }

        if (! $statusStrings) {
            $statusStrings[] = self::OKAY;
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
