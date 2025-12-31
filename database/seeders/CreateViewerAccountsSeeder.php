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
        // Update or create Calendar Viewer Account
        $calendarViewer = User::updateOrCreate(
            ['email' => 'calendar@almajd.com'],
            [
                'name' => 'Calendar Viewer',
                'password' => Hash::make('calendar123'),
                'user_type' => UserType::CalendarViewer,
                'email_verified_at' => now(),
            ]
        );

        // Update or create Certificate Viewer Account
        $certificateViewer = User::updateOrCreate(
            ['email' => 'certificates@almajd.com'],
            [
                'name' => 'Certificate Viewer',
                'password' => Hash::make('certificates123'),
                'user_type' => UserType::CertificateViewer,
                'email_verified_at' => now(),
            ]
        );

        // Delete old accounts if they exist with different emails
        User::where('email', 'calendar.viewer@almajdacademy.com')
            ->where('user_type', UserType::CalendarViewer)
            ->delete();
        
        User::where('email', 'certificate.viewer@almajdacademy.com')
            ->where('user_type', UserType::CertificateViewer)
            ->delete();

        $this->command->info('Viewer accounts created/updated successfully!');
        $this->command->info('');
        $this->command->info('Calendar Viewer Account:');
        $this->command->info('  Email: calendar@almajd.com');
        $this->command->info('  Password: calendar123');
        $this->command->info('');
        $this->command->info('Certificate Viewer Account:');
        $this->command->info('  Email: certificates@almajd.com');
        $this->command->info('  Password: certificates123');
    }
}

