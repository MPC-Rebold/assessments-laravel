<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'specification_grading',
        'specification_grading_threshold',
        'last_synced_at',
        'is_syncing',
    ];
}
