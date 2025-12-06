<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@almajd.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@almajd.com',
                'password' => Hash::make('admin123'),
                'user_type' => UserType::Admin,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@almajd.com');
        $this->command->info('Password: admin123');
    }
}
