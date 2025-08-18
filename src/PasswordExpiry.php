<?php

namespace Beliven\PasswordExpiry;

use Beliven\PasswordExpiry\Events\PasswordExpired;
use Beliven\PasswordExpiry\Events\PasswordExpiring;
use Beliven\PasswordExpiry\Models\PasswordChangelog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordExpiry
{
    private int $daysToExpire;

    public function __construct()
    {
        $this->daysToExpire = config('password-expiry.days_to_expire');
    }

    public function checkPasswords(): void
    {
        $this->expirePasswords();
        $this->checkPasswordExpiration();
    }

    public function tryClearPassword(Model $model): void
    {
        $passwordChangeLog = PasswordChangelog::byModel($model)
            ->expired()
            ->first();

        if (!is_null($passwordChangeLog)) {
            $this->clearPassword($passwordChangeLog);
        }
    }

    public function updatePasswordExpiration(Model $model): PasswordChangelog
    {
        $passwordChangeLog = PasswordChangelog::byModel($model)->first();

        if (is_null($passwordChangeLog)) {
            $passwordChangeLog = new PasswordChangelog;
            $passwordChangeLog->model()->associate($model);
        }

        $passwordChangeLog->expires_at = now()->addDays($this->daysToExpire);
        $passwordChangeLog->save();

        return $passwordChangeLog;
    }

    private function clearPassword(PasswordChangelog $passwordChangelog, ?string $passwordField = null): void
    {
        DB::transaction(function () use ($passwordChangelog) {
            $model = $passwordChangelog->model;

            // If the model does't exist, we can't clear the password
            if (is_null($model)) {
                return;
            }

            $id = $model->getAttribute('id');

            DB::table($model->getTable())
                ->where('id', $id)
                ->update([
                    'password' => Str::random(),
                ]);

            $passwordChangelog->delete();

            PasswordExpired::dispatch($model);
        });
    }

    private function expirePasswords(): void
    {
        PasswordChangelog::with('model')
            ->expired()
            ->chunkById(100, function ($passwordChangelogs) {
                foreach ($passwordChangelogs as $passwordChangelog) {
                    $this->clearPassword($passwordChangelog);
                }
            });
    }

    private function checkPasswordExpiration(): void
    {
        PasswordChangelog::with('model')
            ->upcomingExpiration()
            ->chunkById(100, function ($passwordChangelogs) {
                foreach ($passwordChangelogs as $passwordChangelog) {
                    PasswordExpiring::dispatch($passwordChangelog->model);
                }
            });
    }
}
