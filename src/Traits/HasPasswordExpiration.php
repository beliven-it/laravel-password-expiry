<?php

namespace Beliven\PasswordExpiry\Traits;

use Beliven\PasswordExpiry\PasswordExpiry;

trait HasPasswordExpiration
{
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

        $passwordExpiry = new PasswordExpiry;

        $passwordExpiry->updatePasswordExpiration($this);
    }
}
