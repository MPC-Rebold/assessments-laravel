<?php

use App\Http\Controllers\Auth\ProviderController;
use Tests\SeedProtection;

beforeEach(function () {
    $this->admin_path = config('seed.seed_path') . '/admins.txt';
});

beforeEach(function () {
    SeedProtection::preTest();
});

afterEach(function () {
    SeedProtection::postTest();
});

test('ProviderController isAdmin is true for email in admins.txt', function () {
    $email = 'i_am_an_admin@example.com';

    file_put_contents($this->admin_path, $email);

    expect(ProviderController::isAdmin($email))->toBeTrue();

    unlink($this->admin_path);
});

test('ProviderController isAdmin is true for email among others in admins.txt', function () {
    $email = 'i_am_an_admin@example.com';

    file_put_contents($this->admin_path, fake()->email . "\n" . $email . "\n" . fake()->email);

    expect(ProviderController::isAdmin($email))->toBeTrue();

    unlink($this->admin_path);
});

test('ProviderController isAdmin is true for email not in admins.txt', function () {
    $email = 'i_am_not_admin@example.com';

    file_put_contents($this->admin_path, fake()->email . "\n" . fake()->email);

    expect(ProviderController::isAdmin($email))->toBeFalse();

    unlink($this->admin_path);
});
