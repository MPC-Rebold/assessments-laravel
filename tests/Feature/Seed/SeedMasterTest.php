<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Master;
use App\Services\SeedService;
use Livewire\Volt\Volt;

test('SeedService createMaster creates Master in database and storage', function () {

    $createdMaster = SeedService::createMaster('New Master');

    expect(Master::count())->toBe(1)
        ->and($createdMaster->title)->toBe('New Master')
        ->and($createdMaster->exists())->toBeTrue()
        ->and($createdMaster->status->exists())->toBeTrue();

    SeedService::deleteMaster($createdMaster);
});

test('SeedService deleteMaster deletes Master', function () {
    $createdMaster = SeedService::createMaster('New Master');
    SeedService::deleteMaster($createdMaster);

    expect(Master::count())->toBe(0)
        ->and($createdMaster->exists())->toBeFalse();

});

test('admin.create-master creates Master', function () {
    Master::factory()->count(3)->create();

    Volt::test('admin.create-master')->set('newMasterTitle', 'New Master')->call('createNewMaster');

    $createdMaster = Master::where('title', 'New Master')->first();

    expect(Master::count())->toBe(4)
        ->and($createdMaster->exists())->toBeTrue()
        ->and($createdMaster->status->exists())->toBeTrue();

    SeedService::deleteMaster($createdMaster);
});
