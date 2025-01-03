<?php

namespace Beliven\PasswordExpiry;

use Beliven\PasswordExpiry\Commands\PasswordExpirationCheckCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PasswordExpiryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-password-expiry')
            ->hasConfigFile()
            ->hasMigration('create_model_password_changes')
            ->hasCommand(PasswordExpirationCheckCommand::class);
    }

    public function bootingPackage()
    {
        $this->app->singleton(PasswordExpiry::class, function ($app) {
            return new PasswordExpiry;
        });
    }
}
