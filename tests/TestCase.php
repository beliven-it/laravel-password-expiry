<?php

namespace Beliven\PasswordExpiry\Tests;

use Beliven\PasswordExpiry\PasswordExpiryServiceProvider;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        //$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Schema::create('test_models', function ($table) {
            $table->id();
            $table->string('password')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_models');
        Schema::dropIfExists('model_password_changes');

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            PasswordExpiryServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $migration = include __DIR__ . '/../database/migrations/create_model_password_changes.php.stub';
        $migration->up();
    }
}
