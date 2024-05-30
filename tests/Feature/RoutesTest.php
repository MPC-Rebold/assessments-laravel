<?php

use App\Models\User;

test('GET /admin is 200 for admin', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->withSession(['foo' => 'bar'])
        ->get('/admin')
        ->assertOk();
});


test('GET /admin is 401 for non-admin', function () {
    $nonAdmin = User::factory()->create();

    $this->actingAs($nonAdmin)
        ->withSession(['foo' => 'bar'])
        ->get('/admin')
        ->assertStatus(401);
});
