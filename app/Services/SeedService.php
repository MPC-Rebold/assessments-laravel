<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Assessment;
use App\Models\Master;
use App\Models\Question;
use App\Util\FileHelper;
use Exception;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
        }, glob(FileHelper::getMasterPath($master) . '/*.txt'));
    }

    /**
     * Checks if a master is a valid directory in \database\seed
     *
     * @param string|Master $master title of the course to check
     * @return bool true course is valid, false otherwise
     */
    public static function isValidMaster(string|Master $master): bool
    {
        return is_dir(FileHelper::getMasterPath($master));
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
        $questions_txt = file_get_contents(FileHelper::getAssessmentPathByTitles($masterTitle, $assessmentTitle));

        return self::getQuestionsFromContent($questions_txt);
    }

    /**
     * Returns the questions and answers from string content
     *
     * @param string $content the content to extract questions from
     * @return array of questions
     */
    public static function getQuestionsFromContent(string $content): array
    {
        $exploded = explode('@@', $content);

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

        $assessmentPath = FileHelper::getAssessmentPath($assessment);
        file_put_contents($assessmentPath, $questionsText);
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
            throw new UserException('Course title cannot be empty');
        }

        $newMasterPath = FileHelper::getMasterPath($title);

        if (! is_dir($newMasterPath)) {
            mkdir($newMasterPath);
        } elseif (Master::where('title', $title)->exists()) {
            throw new UserException("Course $title already exists");
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
     *
     * @throws Exception if the master fails to be deleted
     */
    public static function deleteMaster(Master $master): void
    {
        $masterPath = FileHelper::getMasterPath($master);
        FileHelper::rmrf($masterPath);
        $master->delete();
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

        $oldPath = FileHelper::getMasterPath($master);
        $newPath = FileHelper::getMasterPath($newTitle);
        $existingMaster = Master::where('title', $newTitle)->first();

        if ($existingMaster || is_dir($newPath)) {
            throw new UserException("Course $newTitle already exists");
        }

        rename($oldPath, $newPath);
        $master->update(['title' => $newTitle]);
    }

    /**
     * @param Master $master the master to add assessments to
     * @param TemporaryUploadedFile[] $assessments the assessments to add
     * @return Collection<Assessment> of the created Assessments
     *
     * @throws Exception|UserException if assessments fail to be created
     */
    public static function uploadAssessments(Master $master, array $assessments): Collection
    {
        $existingNames = $master->assessments->pluck('title')->toArray();
        $uploadedNamesExtensions = array_map(fn ($assessment) => pathinfo($assessment->getClientOriginalName(), PATHINFO_EXTENSION), $assessments);

        foreach ($uploadedNamesExtensions as $extension) {
            if ($extension !== 'txt') {
                throw new UserException('Only .txt files are allowed');
            }
        }

        $uploadedNames = array_map(fn ($assessment) => trim(pathinfo($assessment->getClientOriginalName(), PATHINFO_FILENAME)), $assessments);

        if (in_array('', $uploadedNames)) {
            throw new UserException('Assessment title cannot be empty');
        }

        $conflictingNames = array_intersect($existingNames, $uploadedNames);
        if (! empty($conflictingNames)) {
            throw new UserException('The assessments: ' . implode(', ', $conflictingNames) . ' have conflicting names');
        }

        $duplicateNames = array_diff_assoc($uploadedNames, array_unique($uploadedNames));
        if (! empty($duplicateNames)) {
            throw new UserException('The assessments: ' . implode(', ', $duplicateNames) . ' have duplicate names');
        }

        foreach ($assessments as $assessment) {
            $content = $assessment->getContent();
            $questions = self::getQuestionsFromContent($content);

            if (count($questions) > 100) {
                throw new UserException('The assessment ' . $assessment->getClientOriginalName() . ' has more than the limit of 100 questions');
            }
        }

        $createdAssessments = [];

        foreach ($assessments as $assessment) {
            $assessmentFileName = $assessment->getClientOriginalName();
            $assessmentTitle = trim(pathinfo($assessmentFileName, PATHINFO_FILENAME));
            $assessmentPath = FileHelper::getAssessmentPathByTitles($master->title, $assessmentTitle);

            if (file_exists($assessmentPath)) {
                throw new UserException("Assessment $assessmentTitle already exists on " . $master->title . '. Try syncing.');
            }

            $createdAssessments[] = self::createAssessment($master->title, $assessmentTitle, $assessment->getContent());
        }

        return collect($createdAssessments);
    }

    /**
     * Creates a new Assessment in the seed directory and database
     *
     * @param string $masterTitle
     * @param string $assessmentTitle
     * @param string $assessmentContents
     * @return Assessment
     *
     * @throws UserException if the assessment fails to be created
     */
    public static function createAssessment(string $masterTitle, string $assessmentTitle, string $assessmentContents): Assessment
    {
        $assessmentTitle = trim($assessmentTitle);

        if ($assessmentTitle === '') {
            throw new UserException('Assessment title cannot be empty');
        }

        if (! preg_match('/^[a-zA-Z0-9\-_ ]+$/', $assessmentTitle)) {
            throw new UserException('Assessment title can only contain letters, numbers, spaces, hyphens, and underscores');
        }

        $assessmentPath = FileHelper::getAssessmentPathByTitles($masterTitle, $assessmentTitle);

        if (file_exists($assessmentPath)) {
            throw new UserException("Assessment $assessmentTitle already exists on " . $masterTitle);
        }

        file_put_contents($assessmentPath, $assessmentContents);

        $master = Master::where('title', $masterTitle)->first();
        $assessment = $master->assessments()->create(['title' => $assessmentTitle]);
        SeedService::seedQuestions($master, $assessment);

        return $assessment;
    }

    /**
     * Deletes the assessment from the seed directory
     *
     * @param Assessment $assessment
     * @return void
     */
    public static function deleteAssessment(Assessment $assessment): void
    {
        $assessmentPath = FileHelper::getAssessmentPath($assessment);
        unlink($assessmentPath);
        $assessment->delete();
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

        $oldPath = FileHelper::getAssessmentPath($assessment);
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

    public static function seedQuestions(Master $master, Assessment $assessment): void
    {
        $questions = SeedService::getQuestions($master->title, $assessment->title);

        foreach ($questions as $question) {
            Question::updateOrCreate(
                ['number' => $question['number'], 'assessment_id' => $assessment->id],
                [
                    'question' => $question['question'],
                    'answer' => $question['answer'],
                ]
            );
        }
    }
}
