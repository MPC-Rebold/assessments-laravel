<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'has_seed',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    public function missing_courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'status_courses');
    }

    public function missing_assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'status_assessments')
            ->withPivot('course_id');
    }

    public function missing_assessment_seeds(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'status_assessments_seeds');
    }
}
