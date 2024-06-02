<?php

namespace Tests;

use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private array $seedDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingsSeeder::class);
    }

    // run this after finishing all tests
    protected function tearDown(): void
    {
        parent::tearDown();

        echo '';
    }
}
