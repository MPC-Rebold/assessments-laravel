<?php

namespace Tests;

use App\Util\FileHelper;
use Exception;

class SeedProtection extends TestCase
{

    private static string $SEED_PATH = __DIR__ . '/../database/seed';
    private static string $BACKUP_PATH = __DIR__ . '/../storage/tmp/seed_backup';
    private static bool $isBackedUp = false;

    /**
     * @throws Exception if the seed directory has not been backed up
     */
    public static function preTest(): void
    {
        if (! self::$isBackedUp) {
            throw new Exception('Seed files have not been backed up');
        }

        FileHelper::rmrf(self::$SEED_PATH);
        mkdir(self::$SEED_PATH);
    }

    public static function postTest(): void
    {
        $postTestFiles = array_diff(scandir(self::$SEED_PATH), ['.', '..']);

        if (! empty($postTestFiles)) {
            self::fail('Seed files have not been restored to their original state: ' . implode(', ', $postTestFiles));
        }
    }

    public static function backupSeed(): void
    {
        FileHelper::recurseCopy(self::$SEED_PATH, self::$BACKUP_PATH);
        self::$isBackedUp = true;
    }

    /**
     * @throws Exception if the seed directory has not been backed up
     */
    public static function restoreSeed(): void
    {
        if (! self::$isBackedUp) {
            throw new Exception('Seed files have not been backed up');
        }

        FileHelper::rmrf(self::$SEED_PATH);
        FileHelper::recurseCopy(self::$BACKUP_PATH, self::$SEED_PATH);
        FileHelper::rmrf(self::$BACKUP_PATH);

        self::$isBackedUp = false;
    }
}
