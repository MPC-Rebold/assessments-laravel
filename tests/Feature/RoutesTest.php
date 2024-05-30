<?php

use App\Models\User;

test('that true is true', function () {
    $admin = User::factory()->create();

    expect(true)->toBeTrue();
});
