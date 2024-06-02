<?php

namespace Tests;

use App\Util\FileHelper;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    protected function setUp(): void
    {
        parent::setUp();

        if (! is_dir(storage_path('tmp'))) {
            mkdir(storage_path('tmp'));
        }

        FileHelper::recurseCopy(database_path('seed'), storage_path('tmp/backup_seed'));

        $this->seed(SettingsSeeder::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $backupFiles = scandir(storage_path('tmp/backup_seed'));
        $postTestFiles = scandir(database_path('seed'));

        $this->assertEquals($backupFiles, $postTestFiles, 'The database seed files were not restored to their original state after the test.');

        FileHelper::rmrf(database_path('seed'));
        FileHelper::recurseCopy(storage_path('tmp/backup_seed'), database_path('seed'));
        FileHelper::rmrf(storage_path('tmp/backup_seed'));
    }
}
