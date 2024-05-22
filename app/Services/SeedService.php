<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Master;
use Exception;

class SeedService
{
    /**
     * Gets the titles of all masters in \database\seed
     *
     * @return array of master titles
     */
    public static function getMasters(): array
    {
        return array_map('basename', glob(database_path('seed') . '/*', GLOB_ONLYDIR));
    }

    /**
     * Gets the titles of all assessments in \database\seed\{masterTitle}
     *
     * @param string $masterTitle
     * @return array
     */
    public static function getAssessments(string $masterTitle): array
    {
        return array_map(function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, glob(database_path('seed/' . $masterTitle) . '/*.txt'));
    }

    /**
     * Checks if a master is a valid directory in \database\seed
     *
     * @param string $courseTitle title of the course to check
     * @return bool true course is valid, false otherwise
     */
    public static function isValidMaster(string $courseTitle): bool
    {
        return is_dir(database_path('seed/' . $courseTitle));
    }

    /**
     * Checks if assignment is a valid txt file in \database\seed\{courseTitle}
     *
     * @param string $courseTitle
     * @param string $assessmentTitle
     * @return bool
     */
    public static function isValidAssessment(string $courseTitle, string $assessmentTitle): bool
    {
        return file_exists(database_path('seed/' . $courseTitle . '/' . $assessmentTitle . '.txt'));
    }

    /**
     * Returns the questions and answers from the file \database\seed\{courseTitle}\{assessmentTitle}.txt
     *
     * @param string $masterTitle
     * @param string $assessmentTitle
     * @return array
     */
    public static function getQuestions(string $masterTitle, string $assessmentTitle): array
    {
        $questions_txt = file_get_contents(database_path('seed/' . $masterTitle . '/' . $assessmentTitle . '.txt'));
        $exploded = explode('@@', $questions_txt);

        $res = [];
        for ($i = 0; $i < count($exploded) - 1; $i += 2) {
            $res[] = [
                'question' => trim($exploded[$i]),
                'answer' => $exploded[$i + 1],
                'number' => $i / 2 + 1,
            ];
        }

        return $res;
    }

    /**
     * Restores the master and its assessments from the database to the seed directory
     *
     * @param Master $master
     * @return void
     */
    public static function restore(Master $master): void
    {
        if (! is_dir(database_path('seed'))) {
            mkdir(database_path('seed'));
        }

        if (! is_dir(database_path('seed/' . $master->title))) {
            mkdir(database_path('seed/' . $master->title));
        }

        foreach ($master->assessments as $assessment) {
            self::writeAssessment($assessment);
        }

        $master->status->update(['has_seed' => true]);
    }

    /**
     * Writes the assessment and its questions to the seed directory
     *
     * @param Assessment $assessment
     * @return void
     */
    public static function writeAssessment(Assessment $assessment): void
    {
        $questionsText = '';
        $questions = $assessment->questions;

        foreach ($questions as $question) {
            $questionsText .= $question->question . "\n@@" . $question->answer . "@@\n\n";
        }

        $assessmentPath = database_path('seed/' . $assessment->master->title . '/' . $assessment->title . '.txt');
        file_put_contents($assessmentPath, $questionsText);
    }

    /**
     * Deletes the assessment from the seed directory
     *
     * @param Assessment $assessment
     * @return void
     */
    public static function deleteAssessment(Assessment $assessment): void
    {
        $assessmentPath = database_path('seed/' . $assessment->master->title . '/' . $assessment->title . '.txt');
        unlink($assessmentPath);
    }

    /**
     * Returns the emails of all admins
     *
     * @return array
     */
    public static function getAdmins(): array
    {
        $admins = file_get_contents(database_path('seed/admins.txt'));
        $admins = explode("\n", $admins);

        return array_filter(array_map('trim', $admins));
    }

    /**
     * Makes a backup of the current sqlite database from /database/database.sqlite to /storage/backups
     * Deletes the oldest backup if the total size of all backups exceeds 500MB.
     *
     * @return void
     */
    public static function backupDatabase(): void
    {
        $backupPath = storage_path('backups');

        if (! is_dir($backupPath)) {
            mkdir($backupPath);
        }

        $totalBackupSize = 0;
        $files = glob($backupPath . '/*.sqlite');
        foreach ($files as $file) {
            $totalBackupSize += filesize($file);
        }

        $maxSize = 500_000_000;

        if ($totalBackupSize > $maxSize && count($files) > 0) {
            unlink($files[0]);
        }

        $backupName = date('Y-m-d-H-i-s') . '.sqlite';
        $backupFile = $backupPath . '/' . $backupName;

        copy(database_path('database.sqlite'), $backupFile);
    }

    /**
     * @throws Exception
     */
    public static function createMaster(string $title): Master
    {
        if (! is_dir(database_path('seed/' . $title))) {
            mkdir(database_path('seed/' . $title));
        } elseif (Master::where('title', $title)->exists()) {
            throw new Exception("Course $title already exists");
        }

        $master = Master::create(['title' => $title]);
        $master->status()->create();

        return $master;
    }

    public static function deleteMaster(Master $master): void
    {
        $masterPath = database_path('seed/' . $master->title);
        self::rmrf($masterPath);
        $master->delete();
    }

    public static function rmrf($dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && ! is_link($dir.'/'.$object)) {
                        self::rmrf($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir. DIRECTORY_SEPARATOR .$object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
