<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'last_schedule_run_at',
        'last_synced_at',
        'is_syncing',
    ];
}
