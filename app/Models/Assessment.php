<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'title',
        'due_at',
        'master_id',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function questionCount(): int
    {
        return $this->questions()->count();
    }
}
