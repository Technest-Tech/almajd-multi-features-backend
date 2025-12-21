<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class FixTeacherWhatsAppCountryCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('Fixing Teacher WhatsApp Country Codes');
        $this->command->info('========================================');
        $this->command->newLine();

        $teachers = User::where('user_type', UserType::Teacher)->get();
        $this->command->info("Found {$teachers->count()} teachers");
        $this->command->newLine();

        $fixed = 0;
        $updated = 0;

        foreach ($teachers as $teacher) {
            $originalWhatsapp = $teacher->whatsapp_number;
            $originalCountry = $teacher->country;

            if (empty($originalWhatsapp)) {
                continue;
            }

            // Normalize WhatsApp number to Egypt format
            $fixedWhatsapp = $this->normalizeToEgypt($originalWhatsapp);
            $needsUpdate = false;

            // Check if WhatsApp needs fixing
            if ($fixedWhatsapp !== $originalWhatsapp) {
                $needsUpdate = true;
                $this->command->info("  Fixing WhatsApp: {$teacher->name}");
                $this->command->info("    {$originalWhatsapp} → {$fixedWhatsapp}");
            }

            // Check if country needs updating
            if ($originalCountry !== 'EG') {
                $needsUpdate = true;
                if ($originalCountry) {
                    $this->command->info("  Fixing Country: {$teacher->name}");
                    $this->command->info("    {$originalCountry} → EG");
                } else {
                    $this->command->info("  Setting Country: {$teacher->name}");
                    $this->command->info("    (empty) → EG");
                }
            }

            if ($needsUpdate) {
                $teacher->whatsapp_number = $fixedWhatsapp;
                $teacher->country = 'EG';
                $teacher->save();
                $fixed++;
                $updated++;
            }
        }

        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('Summary');
        $this->command->info('========================================');
        $this->command->info("Total teachers: {$teachers->count()}");
        $this->command->info("Fixed: {$fixed}");
        $this->command->info("Updated: {$updated}");
        $this->command->newLine();
        $this->command->info('✅ Country code fix completed!');
    }

    /**
     * Normalize WhatsApp number to Egypt format (+20)
     */
    private function normalizeToEgypt(string $whatsapp): string
    {
        // Remove all spaces and non-digit characters except +
        $whatsapp = preg_replace('/[^\d+]/', '', $whatsapp);

        // If it starts with +, check the country code
        if (str_starts_with($whatsapp, '+')) {
            // If it's already +20 (Egypt), return as is
            if (str_starts_with($whatsapp, '+20')) {
                return $whatsapp;
            }
            
            // If it's +1 (US), remove the +1 and add +20
            if (str_starts_with($whatsapp, '+1')) {
                $number = substr($whatsapp, 2);
                // If number starts with 20, it might be a mistake - just add +
                if (str_starts_with($number, '20')) {
                    return '+' . $number;
                }
                // Otherwise, check if it's a valid Egyptian number
                if (preg_match('/^1\d{9}$/', $number)) {
                    return '+20' . $number;
                }
                // If it's 10 digits starting with 2, it might be Egyptian landline
                if (preg_match('/^2\d{9}$/', $number)) {
                    return '+20' . $number;
                }
            }
            
            // If it's another country code, try to extract the number
            // For now, if it's not +20, we'll try to fix it
            $number = substr($whatsapp, 1);
            
            // If number starts with 20, it's already Egyptian format
            if (str_starts_with($number, '20')) {
                return '+' . $number;
            }
            
            // If it's 10 digits starting with 1, it's likely Egyptian mobile
            if (preg_match('/^1\d{9}$/', $number)) {
                return '+20' . $number;
            }
            
            // If it's 10 digits starting with 2, it's likely Egyptian landline
            if (preg_match('/^2\d{9}$/', $number)) {
                return '+20' . $number;
            }
        } else {
            // No + prefix
            // If it starts with 20, add +
            if (str_starts_with($whatsapp, '20')) {
                return '+' . $whatsapp;
            }
            
            // If it starts with 0, replace with +20
            if (str_starts_with($whatsapp, '0')) {
                $number = substr($whatsapp, 1);
                // If it's 10 digits starting with 1, it's Egyptian mobile
                if (preg_match('/^1\d{9}$/', $number)) {
                    return '+20' . $number;
                }
                // If it's 9 digits starting with 1, it's Egyptian mobile without leading 0
                if (preg_match('/^1\d{8}$/', $number)) {
                    return '+20' . $number;
                }
            }
            
            // If it's 10 digits starting with 1, it's likely Egyptian mobile
            if (preg_match('/^1\d{9}$/', $whatsapp)) {
                return '+20' . $whatsapp;
            }
            
            // If it's 9 digits starting with 1, it's likely Egyptian mobile
            if (preg_match('/^1\d{8}$/', $whatsapp)) {
                return '+20' . $whatsapp;
            }
            
            // If it's 10 digits starting with 2, it's likely Egyptian landline
            if (preg_match('/^2\d{9}$/', $whatsapp)) {
                return '+20' . $whatsapp;
            }
        }

        // If we can't determine, assume it's already correct or return as is
        // But if it doesn't start with +20, try to add it
        if (!str_starts_with($whatsapp, '+20')) {
            // Remove any existing + and country code
            $cleanNumber = preg_replace('/^\+?\d{1,3}/', '', $whatsapp);
            $cleanNumber = preg_replace('/[^\d]/', '', $cleanNumber);
            
            // If it's 10 digits starting with 1, add +20
            if (preg_match('/^1\d{9}$/', $cleanNumber)) {
                return '+20' . $cleanNumber;
            }
            
            // If it's 9 digits starting with 1, add +20
            if (preg_match('/^1\d{8}$/', $cleanNumber)) {
                return '+20' . $cleanNumber;
            }
        }

        return $whatsapp;
    }
}




