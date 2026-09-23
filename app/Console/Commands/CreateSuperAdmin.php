<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * There's no UI to create the first super admin (the panel to create one
 * requires a super admin to already exist), so this is the one way in —
 * repeatable and safe to run again in any environment.
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'make:super-admin {email} {name}';

    protected $description = 'Create a platform super admin (no tenant, manages the Super Admin panel)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->argument('name');

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");

            return self::FAILURE;
        }

        $password = $this->secret('Password (min 8 characters)');
        $confirm = $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords did not match.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        User::create([
            'tenant_id' => null,
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'is_super_admin' => true,
            // Deliberately inactive as tenant "staff" — a super admin manages the
            // platform from /super-admin, not any one hostel's /admin panel, and
            // is_active = false keeps EnsureUserIsStaff from letting them into one
            // by accident (which would show unscoped, mixed-tenant data there).
            'is_active' => false,
        ]);

        $this->info("Super admin {$name} <{$email}> created.");

        return self::SUCCESS;
    }
}
