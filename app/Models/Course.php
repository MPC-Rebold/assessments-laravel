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
        return $this->belongsToMany(User::class, 'courses_users');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
