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
        // Create real admin user with secure credentials
        $adminEmail = 'admin@almajdacademy.com';
        $adminPassword = 'Almajd@2024!';
        
        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'System Administrator',
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'user_type' => UserType::Admin,
                'email_verified_at' => now(),
            ]
        );

        // Also create the old mock admin for backward compatibility
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

        $this->command->info('========================================');
        $this->command->info('Admin users created successfully!');
        $this->command->info('========================================');
        $this->command->info('REAL ADMIN CREDENTIALS:');
        $this->command->info('Email: ' . $adminEmail);
        $this->command->info('Password: ' . $adminPassword);
        $this->command->info('========================================');
        $this->command->info('MOCK ADMIN (for development):');
        $this->command->info('Email: admin@almajd.com');
        $this->command->info('Password: admin123');
        $this->command->info('========================================');
    }
}
