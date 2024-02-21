<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function courseForUser(User $user): Course
    {
        return $this->courses()->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();
    }
}
