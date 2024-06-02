<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Master;
use App\Services\SeedService;
use Livewire\Volt\Volt;

test('SeedService createMaster creates Master in database and storage', function () {

    $createdMaster = SeedService::createMaster('__New Master1');

    expect(Master::count())->toBe(1)
        ->and($createdMaster->title)->toBe('__New Master1')
        ->and($createdMaster->exists())->toBeTrue()
        ->and($createdMaster->status->exists())->toBeTrue();

    SeedService::deleteMaster($createdMaster);
});

test('SeedService deleteMaster deletes Master', function () {
    $createdMaster = SeedService::createMaster('__New Master2');
    SeedService::deleteMaster($createdMaster);

    expect(Master::count())->toBe(0)
        ->and($createdMaster->exists())->toBeFalse();

});

test('master.create-master creates Master', function () {
    Master::factory()->count(3)->create();

    Volt::test('master.create-master')->set('newMasterTitle', '__New Master3')->call('createNewMaster');

    $createdMaster = Master::where('title', '__New Master3')->first();

    expect(Master::count())->toBe(4)
        ->and($createdMaster->exists())->toBeTrue()
        ->and($createdMaster->status->exists())->toBeTrue();

    SeedService::deleteMaster($createdMaster);
});

test('master.delete-master deletes Master', function () {
    $createdMaster = SeedService::createMaster('__New Master4');

    expect(Master::count())->toBe(1)
        ->and($createdMaster->exists())->toBeTrue();

    Volt::test('master.delete-master', ['master' => $createdMaster])
        ->assertSet('master', $createdMaster)
        ->call('deleteMaster');

    expect(Master::count())->toBe(0)
        ->and($createdMaster->exists())->toBeFalse();
});
