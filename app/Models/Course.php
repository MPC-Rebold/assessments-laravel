<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'courses_assignments');
    }
}
