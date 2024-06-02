<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Master;
use App\Services\SeedService;
use App\Services\SyncService;
use Tests\SeedProtection;

uses()->group('sync');

beforeAll(function () {
    SeedProtection::backupSeed();
    dd("hello");
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

test('Sync after creating master directory creates Master in database', function () {
    mkdir(database_path('seed/__NewMaster'));

    SyncService::sync();

    expect(Master::count())->toBe(1)
        ->and(Master::first()->exists())->toBeTrue()
        ->and(Master::first()->title)->toBe('__NewMaster')
        ->and(Master::first()->status->exists())->toBeTrue();

    SeedService::deleteMaster(Master::first());
});

test ('that true is true', function () {
    expect(true)->toBeTrue();
});
