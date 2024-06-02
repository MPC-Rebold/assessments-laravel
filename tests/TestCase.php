<?php

namespace Tests;

use App\Util\FileHelper;
use Database\Seeders\SettingsSeeder;
use Exception;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private array $startingSeedDirectoryState;

    protected function setUp(): void
    {
        parent::setUp();

        $this->startingSeedDirectoryState = scandir(database_path('seed'));

        $this->seed(SettingsSeeder::class);
    }

    /**
     * @throws Exception if any anomalies are detected in the seed directory after testing
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $messages = [];
        try {
            $this->checkResidualSeedFiles();
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
        }

        try {
            $this->checkUnanticipatedDeletedSeedFiles();
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
        }

        if (! empty($messages)) {
            throw new Exception(implode("\n", $messages));
        }
    }

    /**
     * @throws Exception if any residual seed files are left in the seed directory after testing
     */
    private function checkResidualSeedFiles(): void
    {
        $seedDirectoryEndState = scandir(database_path('seed'));
        $deletedFiles = [];

        foreach ($seedDirectoryEndState as $file) {
            if (! in_array($file, $this->startingSeedDirectoryState)) {
                $deletedFiles[] = $file;
                FileHelper::rmrf(database_path('seed/' . $file));
            }
        }

        if (! empty($deletedFiles)) {
            throw new Exception('Had to delete residual seed files: ' . implode(', ', $deletedFiles));
        }
    }

    /**
     * @throws Exception
     */
    private function checkUnanticipatedDeletedSeedFiles(): void
    {
        $seedDirectoryEndState = scandir(database_path('seed'));
        $unanticipatedDeletedFiles = [];

        foreach ($this->startingSeedDirectoryState as $file) {
            if (! in_array($file, $seedDirectoryEndState)) {
                $unanticipatedDeletedFiles[] = $file;
            }
        }

        if (! empty($unanticipatedDeletedFiles)) {
            throw new Exception('Unanticipated deleted seed files: ' . implode(', ', $unanticipatedDeletedFiles));
        }
    }
}
