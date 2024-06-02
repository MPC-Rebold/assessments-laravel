<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Assessment;
use App\Models\Question;
use App\Services\SeedService;
use App\Util\FileHelper;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Volt;
use Tests\SeedProtection;

beforeAll(function () {
    SeedProtection::backupSeed();
});

beforeEach(function () {
    SeedProtection::preTest();
});

afterEach(function () {
    SeedProtection::postTest();
});

afterAll(function () {
    SeedProtection::restoreSeed();
});

test('SeedService createAssessment creates Assessment in database and storage', function () {
    $master = SeedService::createMaster('__NewMaster');
    $createdAssessment = SeedService::createAssessment('__NewMaster', '__NewAssessment', 'question@@answer@@');

    expect(Assessment::count())->toBe(1)
        ->and($createdAssessment->exists())->toBeTrue()
        ->and($createdAssessment->master->title)->toBe('__NewMaster')
        ->and($createdAssessment->questions->count())->toBe(1);

    SeedService::deleteMaster($master);
});

test('SeedService deleteAssessment deletes Assessment', function () {
    $master = SeedService::createMaster('__NewMaster');
    $createdAssessment = SeedService::createAssessment('__NewMaster', '__NewAssessment', 'question@@answer@@');
    $createdAssessmentPath = FileHelper::getAssessmentPath($createdAssessment);

    SeedService::deleteAssessment($createdAssessment);

    expect(Assessment::count())->toBe(0)
        ->and(Question::count())->toBe(0)
        ->and(file_exists($createdAssessmentPath))->toBeFalse();

    SeedService::deleteMaster($master);
});

test('master.upload-assessments creates single Assessment', function () {
    $master = SeedService::createMaster('__NewMaster');

    $assessments = [TemporaryUploadedFile::fake()->create('__NewAssessment.txt', 'question@@answer@@')];

    Volt::test('master.upload-assessments', ['master' => $master])
        ->set('uploadedAssessments', $assessments)
        ->call('saveUploadedAssessments');

    expect(Assessment::count())->toBe(1)
        ->and($master->assessments->count())->toBe(1)
        ->and($master->assessments->first()->questions->count())->toBe(1);

    SeedService::deleteMaster($master);
});

// TODO uploading assessment to connected course changes the max points on canvas

test('master.upload-assessments creates multiple Assessments', function () {
    $master = SeedService::createMaster('__NewMaster');

    $assessments = [
        TemporaryUploadedFile::fake()->create('__NewAssessment1.txt', 'question1@@answer1@@'),
        TemporaryUploadedFile::fake()->create('__NewAssessment2.txt', 'question1@@answer1@@question1@@answer2@@'),
        TemporaryUploadedFile::fake()->create('__NewAssessment3.txt', 'question1@@answer1@@question2@@answer2@@question3@@answer3@@'),
    ];

    Volt::test('master.upload-assessments', ['master' => $master])
        ->set('uploadedAssessments', $assessments)
        ->call('saveUploadedAssessments');

    expect(Assessment::count())->toBe(3)
        ->and($master->assessments->count())->toBe(3)
        ->and($master->assessments->where('title', '__NewAssessment1')->first()->questions->count())->toBe(1)
        ->and($master->assessments->where('title', '__NewAssessment2')->first()->questions->count())->toBe(2)
        ->and($master->assessments->where('title', '__NewAssessment3')->first()->questions->count())->toBe(3);

    SeedService::deleteMaster($master);
});

test('assessment.delete-assessment deletes Assessment', function () {
    $createdMaster = SeedService::createMaster('__NewMaster');
    $createdAssessment = SeedService::createAssessment('__NewMaster', '__NewAssessment', 'question@@answer@@');

    Volt::test('assessment.delete-assessment', ['assessment' => $createdAssessment])
        ->call('deleteAssessment');

    expect(Assessment::count())->toBe(0)
        ->and($createdAssessment->exists())->toBeFalse()
        ->and(Question::count())->toBe(0);

    SeedService::deleteMaster($createdMaster);
});
