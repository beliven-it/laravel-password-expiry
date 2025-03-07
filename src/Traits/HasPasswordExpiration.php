<?php

namespace Beliven\PasswordExpiry\Traits;

use Beliven\PasswordExpiry\Facades\PasswordExpiry;
use Beliven\PasswordExpiry\Models\PasswordChangelog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphOne;

// @phpstan-ignore-next-line
trait HasPasswordExpiration
{
    public function passwordChangelog(): MorphOne
    {
        return $this->morphOne(PasswordChangelog::class, 'model');
    }

    public function tryClearPassword(): void
    {
        PasswordExpiry::tryClearPassword($this);
    }

    public function getPasswordExpiresAtAttribute(): ?Carbon
    {
        return $this->passwordChangelog?->expires_at ?? null;
    }

    protected static function bootHasPasswordExpiration(): void
    {
        static::saved(function ($model) {
            $model->handleSaved();
        });
    }

    protected function handleSaved()
    {
        if (!$this->isDirty('password')) {
            return;
        }

        PasswordExpiry::updatePasswordExpiration($this);
    }
}
