<?php

namespace Tests;

use App\Util\FileHelper;
use Exception;

class SeedProtection extends TestCase
{
    public static function preTest(): void
    {
        if (! is_dir(storage_path('tmp'))) {
            mkdir(storage_path('tmp'));
        }

        FileHelper::recurseCopy(database_path('seed'), storage_path('tmp/backup_seed'));
    }

    /**
     * @throws Exception if seed files have not been restored to their original state
     */
    public static function postTest(): void
    {
        $backupFiles = scandir(storage_path('tmp/backup_seed'));
        $postTestFiles = scandir(database_path('seed'));

        FileHelper::rmrf(database_path('seed'));
        FileHelper::recurseCopy(storage_path('tmp/backup_seed'), database_path('seed'));
        FileHelper::rmrf(storage_path('tmp/backup_seed'));

        self::assertEquals($backupFiles, $postTestFiles);
    }
}
