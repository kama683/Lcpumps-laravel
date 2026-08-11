<?php

namespace App\Console\Commands\Admin;

use App\Models\AdminUser;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:seed-user {--username=} {--password=}')]
#[Description('Create or update the single admin account (bcrypt password, admin_users table).')]
class SeedAdminUser extends Command
{
    public function handle(): int
    {
        $username = $this->option('username') ?: text('Admin username', required: true);
        $password = $this->option('password') ?: password('Admin password (min. 8 characters)', required: true);

        if (mb_strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        AdminUser::updateOrCreate(
            ['username' => $username],
            ['password_hash' => Hash::make($password)]
        );

        $this->info("Admin user \"{$username}\" is set.");

        return self::SUCCESS;
    }
}
