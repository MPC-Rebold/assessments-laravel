<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Master;
use App\Services\SeedService;
use App\Services\SyncService;
use App\Util\FileHelper;
use Tests\SeedProtection;

uses()->group('sync');

beforeEach(function () {
    SeedProtection::preTest();
});

afterEach(function () {
    SeedProtection::postTest();
});

test('Sync after creating master directory creates Master in database', function () {
    mkdir(config('seed.seed_path') . '/__NewMaster');

    SyncService::sync();

    expect(Master::count())->toBe(1)
        ->and(Master::first()->title)->toBe('__NewMaster')
        ->and(Master::first()->status->exists())->toBeTrue();

    SeedService::deleteMaster(Master::first());
});

test('Sync after deleting master directory assigns missing status', function () {
    $createdMaster = SeedService::createMaster('__NewMaster');

    rmdir(config('seed.seed_path'). '/__NewMaster');

    SyncService::sync();

    expect(Master::count())->toBe(1)
        ->and($createdMaster->status->exists())->toBeTrue()
        ->and($createdMaster->status->has_seed)->toBeFalsy();
});

test('Sync after creating assessment file creates Assessment with Questions in database', function () {
    $createdMaster = SeedService::createMaster('__NewMaster');

    file_put_contents(config('seed.seed_path') . '/__NewMaster/__NewAssessment.txt', "question\n@@answer@@");

    SyncService::sync();

    expect($createdMaster->assessments->count())->toBe(1)
        ->and($createdMaster->assessments->first()->title)->toBe('__NewAssessment')
        ->and($createdMaster->assessments->first()->questions->count())->toBe(1)
        ->and($createdMaster->assessments->first()->questions->first()->question)->toBe('question')
        ->and($createdMaster->assessments->first()->questions->first()->answer)->toBe('answer');

    SeedService::deleteMaster($createdMaster);
});

test('Sync after deleting assessment file assigns missing assessment status', function () {
    $createdMaster = SeedService::createMaster('__NewMaster');
    $createdAssessment = SeedService::createAssessment($createdMaster->title, '__NewAssessment', 'question@@answer@@');

    unlink(FileHelper::getAssessmentPathByTitles($createdMaster->title, $createdAssessment->title));

    SyncService::sync();

    expect($createdMaster->assessments->count())->toBe(1)
        ->and($createdMaster->status->missing_assessment_seeds->count())->toBe(1)
        ->and($createdMaster->status->missing_assessment_seeds->first()->title)->toBe('__NewAssessment');

    SeedService::deleteMaster($createdMaster);
});
