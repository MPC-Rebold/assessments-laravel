<?php

namespace Tests;

use App\Util\FileHelper;

class SeedProtection extends TestCase
{
    public static function preTest(): void
    {
        FileHelper::rmrf(getenv('SEED_PATH'));
        mkdir(getenv('SEED_PATH'));
    }

    public static function postTest(): void
    {
        $postTestFiles = array_diff(scandir(getenv('SEED_PATH')), ['.', '..']);

        if (! empty($postTestFiles)) {
            self::fail('Seed files have not been restored to their original state: ' . implode(', ', $postTestFiles));
        }
    }
}
