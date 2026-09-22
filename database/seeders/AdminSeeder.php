<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@nestaypg.in');
        $password = env('ADMIN_PASSWORD', 'admin123');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Nestay Admin',
                'password' => Hash::make($password),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command->info("✅ Admin user created: {$email}");
        } else {
            $this->command->info("ℹ️ Admin user already exists: {$email} (password unchanged)");
        }
    }
}
