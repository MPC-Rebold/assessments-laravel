<?php

namespace App\Services;

use App\Exceptions\UserException;
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
     * @param string|Master $master
     * @return array of assessment titles
     */
    public static function getAssessments(string|Master $master): array
    {
        return array_map(function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, glob(self::getMasterPath($master) . '/*.txt'));
    }

    /**
     * Checks if a master is a valid directory in \database\seed
     *
     * @param string|Master $master title of the course to check
     * @return bool true course is valid, false otherwise
     */
    public static function isValidMaster(string|Master $master): bool
    {
        return is_dir(self::getMasterPath($master));
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

        $assessmentPath = self::getAssessmentPath($assessment);
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
        $assessmentPath = self::getAssessmentPath($assessment);
        unlink($assessmentPath);
        $assessment->delete();
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
     * Creates a new master in the seed directory and database
     *
     * @throws Exception if the master already exists
     */
    public static function createMaster(string $title): Master
    {
        $title = trim($title);

        if ($title === '') {
            throw new Exception('Course title cannot be empty');
        }

        $newMasterPath = self::getMasterPath($title);

        if (! is_dir($newMasterPath)) {
            mkdir($newMasterPath);
        } elseif (Master::where('title', $title)->exists()) {
            throw new Exception("Course $title already exists");
        }

        $master = Master::create(['title' => $title]);
        $master->status()->create();

        return $master;
    }

    /**
     * Deletes the master from the seed directory and database
     *
     * @param Master $master the master to delete
     * @return void
     */
    public static function deleteMaster(Master $master): void
    {
        $masterPath = self::getMasterPath($master);
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

    /**
     * Renames the master in the seed directory and database
     *
     * @param Master $master the master to rename
     * @param string $newTitle the new title of the master
     * @return void
     *
     * @throws Exception if a Master with the new title already exists
     */
    public static function renameMaster(Master $master, string $newTitle): void
    {
        $newTitle = trim($newTitle);

        $oldPath = self::getMasterPath($master);
        $newPath = self::getMasterPath($newTitle);
        $existingMaster = Master::where('title', $newTitle)->first();

        if ($existingMaster || is_dir($newPath)) {
            throw new Exception("Course $newTitle already exists");
        }

        rename($oldPath, $newPath);
        $master->update(['title' => $newTitle]);
    }

    /**
     * Renames the assessment in the seed directory and database
     *
     * @param Assessment $assessment the assessment to rename
     * @param string $newTitle the new title of the assessment
     * @return Assessment the renamed assessment
     *
     * @throws Exception if an Assessment with new title in the same course already exists
     */
    public static function renameAssessment(Assessment $assessment, string $newTitle): Assessment
    {
        $newTitle = trim($newTitle);

        if ($newTitle === '') {
            throw new UserException('Assessment title cannot be empty');
        }

        if (! preg_match('/^[a-zA-Z0-9\-_ ]+$/', $newTitle)) {
            throw new UserException('Assessment title can only contain letters, numbers, spaces, hyphens, and underscores');
        }

        $oldPath = self::getAssessmentPath($assessment);
        $newPath = database_path('seed/' . $assessment->master->title . '/' . $newTitle . '.txt');
        $existingAssessment = Assessment::where([
            ['title', $newTitle],
            ['master_id', $assessment->master_id],
        ])->first();

        if ($existingAssessment || file_exists($newPath)) {
            throw new UserException("Assessment $newTitle already exists on " . $assessment->master->title);
        }

        rename($oldPath, $newPath);
        $assessment->update(['title' => $newTitle]);

        return $assessment;
    }

    /**
     * Returns the path of the assessment in the seed directory
     *
     * @param Assessment $assessment the assessment to get the path of
     * @return string the path of the assessment
     */
    public static function getAssessmentPath(Assessment $assessment): string
    {
        return self::getAssessmentPathByTitles($assessment->master->title, $assessment->title);
    }

    /**
     * Returns the path of the assessment in the seed directory
     *
     * @param string $masterTitle the title of the master
     * @param string $assessmentTitle the title of the assessment
     * @return string the path of the assessment in the seed directory
     */
    public static function getAssessmentPathByTitles(string $masterTitle, string $assessmentTitle): string
    {
        return database_path('seed/' . $masterTitle . '/' . $assessmentTitle . '.txt');
    }

    /**
     * Returns the path of the master in the seed directory
     *
     * @param Master|string $master the master to get the path of as a title string or a Master object
     * @return string the path of the master in the seed directory
     */
    public static function getMasterPath(Master|string $master): string
    {
        if ($master instanceof Master) {
            return database_path('seed/' . $master->title);
        }

        return database_path('seed/' . $master);
    }
}
