<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Master;
use App\Services\SeedService;
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

test('SeedService createMaster creates Master in database and storage', function () {
    $createdMaster = SeedService::createMaster('__NewMaster');

    expect(Master::count())->toBe(1)
        ->and($createdMaster->title)->toBe('__NewMaster')
        ->and($createdMaster->exists())->toBeTrue()
        ->and($createdMaster->status->exists())->toBeTrue();

    SeedService::deleteMaster($createdMaster);
});

test('SeedService deleteMaster deletes Master', function () {
    $createdMaster = SeedService::createMaster('__NewMaster');
    SeedService::deleteMaster($createdMaster);

    expect(Master::count())->toBe(0)
        ->and($createdMaster->exists())->toBeFalse();

});

test('master.create-master creates Master', function () {
    Master::factory()->count(3)->create();

    Volt::test('master.create-master')->set('newMasterTitle', '__NewMaster')->call('createNewMaster');

    $createdMaster = Master::where('title', '__NewMaster')->first();

    expect(Master::count())->toBe(4)
        ->and($createdMaster->exists())->toBeTrue()
        ->and($createdMaster->status->exists())->toBeTrue();

    SeedService::deleteMaster($createdMaster);
});

test('master.delete-master deletes Master', function () {
    $createdMaster = SeedService::createMaster('__NewMaster');

    Volt::test('master.delete-master', ['master' => $createdMaster])
        ->assertSet('master', $createdMaster)
        ->call('deleteMaster');

    expect(Master::count())->toBe(0)
        ->and($createdMaster->exists())->toBeFalse();
});
