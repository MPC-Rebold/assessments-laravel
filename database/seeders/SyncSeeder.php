<?php

namespace Database\Seeders;

use App\Services\SyncService;
use Exception;
use Illuminate\Database\Seeder;

class SyncSeeder extends Seeder
{
    /**
     * Run the sync
     *
     * @throws Exception
     */
    public function run(): void
    {
        SyncService::sync();
    }
}
