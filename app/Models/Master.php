<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Master extends Model
{
    protected $fillable = [
        'title',
    ];

    protected $casts = [
        'valid_students' => 'array',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
