<?php

namespace Beliven\PasswordExpiry\Commands;

use Beliven\PasswordExpiry\Facades\PasswordExpiry;
use Illuminate\Console\Command;

class PasswordExpirationCheckCommand extends Command
{
    public $signature = 'password-expiry:check';

    public $description = 'Password expiration checker';

    public function handle(): int
    {
        PasswordExpiry::checkPasswords();

        return self::SUCCESS;
    }
}
