<?php

namespace Beliven\PasswordExpiry\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Beliven\PasswordExpiry\PasswordExpiry
 */
class PasswordExpiry extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Beliven\PasswordExpiry\PasswordExpiry::class;
    }
}
