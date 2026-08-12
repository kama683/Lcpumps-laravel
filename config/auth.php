<?php

use App\Models\AdminUser;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This app has exactly one kind of account — the single admin user in
    | admin_users (seeded via `php artisan admin:seed-user`). There's no
    | public registration/login, so the Laravel skeleton's default "web"
    | guard, "users" provider, and users/password_reset_tokens tables were
    | never created — see the (repurposed) 0001_01_01_000000 migration,
    | which now only creates the sessions table. "admin" is the only guard
    | that exists, so it's also the default.
    |
    */

    'defaults' => [
        'guard' => 'admin',
        'passwords' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admin_users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'admin_users' => [
            'driver' => 'eloquent',
            'model' => AdminUser::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | No self-service password reset flow — a single admin account is reset
    | by re-running `admin:seed-user`, not by email token. Left empty rather
    | than removed so Password::broker() fails loudly (missing config) if
    | something ever tries to use it, instead of silently misconfiguring.
    |
    */

    'passwords' => [],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
