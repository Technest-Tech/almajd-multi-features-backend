<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateViewerAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Calendar Viewer Account
        $calendarViewer = User::firstOrCreate(
            ['email' => 'calendar.viewer@almajdacademy.com'],
            [
                'name' => 'Calendar Viewer',
                'password' => Hash::make('CalendarViewer@2025'),
                'user_type' => UserType::CalendarViewer,
                'email_verified_at' => now(),
            ]
        );

        // Create Certificate Viewer Account
        $certificateViewer = User::firstOrCreate(
            ['email' => 'certificate.viewer@almajdacademy.com'],
            [
                'name' => 'Certificate Viewer',
                'password' => Hash::make('CertificateViewer@2025'),
                'user_type' => UserType::CertificateViewer,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Viewer accounts created successfully!');
        $this->command->info('');
        $this->command->info('Calendar Viewer Account:');
        $this->command->info('  Email: calendar.viewer@almajdacademy.com');
        $this->command->info('  Password: CalendarViewer@2025');
        $this->command->info('');
        $this->command->info('Certificate Viewer Account:');
        $this->command->info('  Email: certificate.viewer@almajdacademy.com');
        $this->command->info('  Password: CertificateViewer@2025');
    }
}

