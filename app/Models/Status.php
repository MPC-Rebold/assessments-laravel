<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Status extends Model
{
    protected $fillable = [
        'master_id',
        'missing_courses',
        'missing_assessments',
    ];

    protected $casts = [
        'missing_courses' => 'array',
        'missing_assessments' => 'array',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }
}
