<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'id',
        'title',
        'valid_students',
    ];

    protected $casts = [
        'valid_students' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function sectionForUser(string $userEmail): Section
    {
        return $this->sections->first(function (Section $section) use ($userEmail) {
            return in_array($userEmail, $section->valid_students);
        });
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
