<?php

namespace Database\Seeders;

use App\Models\Settings;
use App\Services\SyncService;
use Illuminate\Database\Seeder;

class SyncSeeder extends Seeder
{
    /**
     * Run the sync
     * @throws \Exception
     */
    public function run(): void
    {
        SyncService::sync();
    }
}
