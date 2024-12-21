<?php

namespace Beliven\PasswordExpiry\Traits;

use Beliven\PasswordExpiry\Facades\PasswordExpiry;
use Beliven\PasswordExpiry\Models\PasswordChangelog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPasswordExpiration
{
    public function passwordChangelog(): MorphOne
    {
        return $this->morphOne(PasswordChangelog::class, 'model');
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
