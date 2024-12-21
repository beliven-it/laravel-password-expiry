<?php

namespace Beliven\PasswordExpiry\Tests;

use Beliven\PasswordExpiry\Events\PasswordExpired;
use Beliven\PasswordExpiry\Events\PasswordExpiring;
use Beliven\PasswordExpiry\Facades\PasswordExpiry as PasswordExpiryFacade;
use Beliven\PasswordExpiry\Models\PasswordChangelog;
use Beliven\PasswordExpiry\PasswordExpiry;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    PasswordChangelog::truncate();
    $this->passwordExpiry = new PasswordExpiry;
});

describe('Password Expiry Command', function () {
    it('should run the command for check passwords', function () {
        $this->artisan('password-expiry:check')
            ->assertExitCode(0);
    });
});

describe('Password Expiry Facade', function () {
    it('can use the facade', function () {
        $model = new TestModelWithTrait;
        $model->id = 10;
        $model->password = 'password';
        $model->save();

        $passwordChangelog = PasswordChangelog::byModel($model)->first();
        $passwordChangelog->expires_at = now()->subDays(1);
        $passwordChangelog->save();

        PasswordExpiryFacade::tryClearPassword($model);

        expect(PasswordChangelog::byModel($model)->first())->toBeNull();
    });
});

describe('Password Expiry Model', function () {
    it('should filter by model', function () {
        $model01 = new TestModel;
        $model01->id = 1;
        $model01->save();

        $model02 = new TestModel;
        $model02->id = 2;
        $model02->save();

        $passwordChangelog = new PasswordChangelog;
        $passwordChangelog->expires_at = now()->addDays(1);
        $passwordChangelog->model()->associate($model01);
        $passwordChangelog->save();

        $passwordChangelog = new PasswordChangelog;
        $passwordChangelog->expires_at = now()->addDays(1);
        $passwordChangelog->model()->associate($model02);
        $passwordChangelog->save();

        expect(PasswordChangelog::count())->toBe(2);

        $result = PasswordChangelog::byModel($model02)->get();

        expect($result->count())->toBe(1)
            ->and($result[0]->model_id)->toBe($model02->id);
    });

    it('should take only expired entries', function () {
        $dates = [
            now()->subDays(1),
            now()->subDays(2),
            now()->addDays(1),
            now(),
        ];

        foreach ($dates as $index => $date) {
            $model = new TestModel;
            $model->id = $index + 1;
            $model->save();

            $passwordChangelog = new PasswordChangelog;
            $passwordChangelog->expires_at = $date;
            $passwordChangelog->model()->associate($model);
            $passwordChangelog->save();
        }

        expect(PasswordChangelog::expired()->count())->toBe(2);
    });

    it('should take only upcoming expiration entries', function () {
        $dates = [
            now()->addDays(6),
            now()->addDays(config('password-expiry.days_to_notify_expiration')),
            now()->addDays(8),
            now()->addDays(config('password-expiry.days_to_notify_expiration')),
            now()->subDays(8),
            now(),
        ];

        foreach ($dates as $index => $date) {
            $model = new TestModel;
            $model->id = $index + 1;
            $model->save();

            $passwordChangelog = new PasswordChangelog;
            $passwordChangelog->expires_at = $date->format('Y-m-d');
            $passwordChangelog->model()->associate($model);
            $passwordChangelog->save();
        }

        expect(PasswordChangelog::upcomingExpiration()->count())->toBe(2);
    });
});

describe('Password Expiry Trait', function () {
    it('should return the password expiring date', function () {
        $model = new TestModelWithTrait;
        $model->password = 'password';
        $model->id = 1;
        $model->save();

        $passwordExpiringDate = $model->password_expires_at;

        $expectedExpirationDate = now()
            ->addDays(config('password-expiry.days_to_expire'));

        expect($passwordExpiringDate->isSameDay($expectedExpirationDate))->toBeTrue();
    });

    it('should clear a password', function () {
        $model = new TestModelWithTrait;
        $model->password = 'password';
        $model->id = 1;
        $model->save();

        expect($model->password_expires_at)->not->toBeNull();

        $passwordChangelog = $model->passwordChangelog;
        $passwordChangelog->expires_at = now()->subDays(9);
        $passwordChangelog->save();

        $model->tryClearPassword();

        $model->refresh();

        expect($model->password_expires_at)->toBeNull();
    });

    it('should create a password changelog on model creation', function () {
        $model = new TestModelWithTrait;
        $model->password = 'password';
        $model->id = 1;
        $model->save();

        $passwordChangelogs = PasswordChangelog::byModel($model)->get();

        $expectedExpirationDate = now()
            ->addDays(config('password-expiry.days_to_expire'));

        expect($passwordChangelogs->count())->toBe(1)
            ->and($passwordChangelogs[0]->expires_at->isSameDay($expectedExpirationDate))->toBeTrue;
    });

    it('should update a password changelog on model password update', function () {
        $model = new TestModelWithTrait;
        $model->id = 1;
        $model->password = 'password';
        $model->save();

        $firstExpirationDate = now()->subDays(1);
        $passwordChangelog = PasswordChangelog::byModel($model)->first();
        $passwordChangelog->expires_at = $firstExpirationDate;
        $passwordChangelog->save();

        $model->password = 'new-password';
        $model->save();
        $expectedExpirationDate = PasswordChangelog::byModel($model)
            ->first()
            ->expires_at;

        expect($expectedExpirationDate->isSameDay($firstExpirationDate))->toBeFalse();
    });

    it('should not update a password changelog without a password update', function () {
        $model = new TestModelWithTrait;
        $model->id = 1;
        $model->password = 'password';
        $model->save();

        $firstExpirationDate = now()->subDays(1);
        $passwordChangelog = PasswordChangelog::byModel($model)->first();
        $passwordChangelog->expires_at = $firstExpirationDate;
        $passwordChangelog->save();

        $model->password = 'password';
        $model->save();
        $expectedExpirationDate = PasswordChangelog::byModel($model)
            ->first()
            ->expires_at;

        expect($expectedExpirationDate->isSameDay($firstExpirationDate))->toBeTrue();
    });
});

describe('Password Expiry Methods', function () {
    it('should clear a password if expired', function () {
        $model = new TestModelWithTrait;
        $model->id = 1;
        $model->password = Hash::make('password');
        $model->save();

        $passwordChangelog = PasswordChangelog::byModel($model)->first();
        $passwordChangelog->expires_at = now()->subDays(1);
        $passwordChangelog->save();

        $model->refresh();
        expect(Hash::isHashed($model->password))->toBeTrue();

        $this->passwordExpiry->tryClearPassword($model);

        $passwordChangelog = PasswordChangelog::byModel($model)->first();

        expect($passwordChangelog)->toBeNull();

        $model->refresh();
        expect(Hash::isHashed($model->password))->toBeFalse();
    });

    it('should not clear a password if not expired', function () {
        $model = new TestModelWithTrait;
        $model->id = 1;
        $model->password = Hash::make('password');
        $model->save();

        $passwordChangelog = PasswordChangelog::byModel($model)->first();
        $passwordChangelog->expires_at = now()->addDays(1);
        $passwordChangelog->save();

        $model->refresh();
        expect(Hash::isHashed($model->password))->toBeTrue();

        $this->passwordExpiry->tryClearPassword($model);

        $passwordChangelog = PasswordChangelog::byModel($model)->first();

        expect($passwordChangelog)->not()->toBeNull();

        $model->refresh();
        expect(Hash::isHashed($model->password))->toBeTrue();
    });

    it('should dispatch events for any model with password in expiration', function () {
        $dates = [
            now()->addDays(config('password-expiry.days_to_notify_expiration')),
            now()->addDays(config('password-expiry.days_to_notify_expiration')),
            now(),
        ];

        foreach ($dates as $index => $date) {
            $model = new TestModel;
            $model->id = $index + 1;
            $model->save();

            $passwordChangelog = new PasswordChangelog;
            $passwordChangelog->expires_at = $date;
            $passwordChangelog->model()->associate($model);
            $passwordChangelog->save();
        }

        Event::fake();
        $this->passwordExpiry->checkPasswords();

        Event::assertDispatchedTimes(PasswordExpiring::class, 2);
        Event::assertDispatchedTimes(PasswordExpired::class, 0);
    });

    it('should clear any password expired for any model', function () {
        $dates = [
            now()->subDays(10),
            now()->addDays(190),
        ];

        foreach ($dates as $date) {
            $model = new TestModel;
            $model->save();

            $passwordChangelog = new PasswordChangelog;
            $passwordChangelog->expires_at = $date;
            $passwordChangelog->model()->associate($model);
            $passwordChangelog->save();
        }

        Event::fake();
        $this->passwordExpiry->checkPasswords();

        Event::assertDispatchedTimes(PasswordExpiring::class, 0);
        Event::assertDispatchedTimes(PasswordExpired::class, 1);
    });
});
