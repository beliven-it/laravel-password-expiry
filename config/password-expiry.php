<?php

// config for Beliven/PasswordExpiry
return [
    'days_to_notify_expiration' => (int) env('DAYS_TO_NOTIFY_EXPIRATION', 7),
    'days_to_expire'            => (int) env('DAYS_TO_EXPIRE', 90),
];
