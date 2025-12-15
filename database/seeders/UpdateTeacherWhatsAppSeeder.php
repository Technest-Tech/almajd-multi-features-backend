<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UpdateTeacherWhatsAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('Updating Teacher WhatsApp Numbers');
        $this->command->info('========================================');
        $this->command->newLine();

        // Try multiple possible locations for the teachers.sql file
        $possiblePaths = [
            base_path('database/data/teachers.sql'),
            storage_path('app/imports/teachers.sql'),
            '/Users/ahmedomar/Downloads/teachers.sql',
            base_path('teachers.sql'),
        ];

        $sqlFile = null;
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $sqlFile = $path;
                break;
            }
        }

        if (!$sqlFile) {
            $this->command->error("❌ Teachers SQL file not found. Tried:");
            foreach ($possiblePaths as $path) {
                $this->command->info("  - {$path}");
            }
            $this->command->newLine();
            $this->command->info("Please place teachers.sql in one of these locations.");
            return;
        }

        $this->command->info("✓ Found SQL file: {$sqlFile}");
        $this->command->newLine();

        // Extract teachers data from SQL file
        $teachers = $this->extractTeachersFromSql($sqlFile);
        $this->command->info("Found " . count($teachers) . " teachers in SQL file");
        $this->command->newLine();

        // Get all teachers from database
        $dbTeachers = User::where('user_type', UserType::Teacher)->get();
        $this->command->info("Found " . $dbTeachers->count() . " teachers in database");
        $this->command->newLine();

        // Match and update
        $matched = 0;
        $updated = 0;
        $notMatched = [];
        $processedUserIds = []; // Track which users we've already processed

        // First pass: exact matches only
        foreach ($teachers as $sqlTeacher) {
            $sqlName = trim($sqlTeacher['name']);
            $whatsapp = $this->normalizeWhatsApp($sqlTeacher['whatsapp']);

            // Try exact match first
            $matchedUser = $dbTeachers->first(function ($user) use ($sqlName, $processedUserIds) {
                return trim($user->name) === $sqlName && !in_array($user->id, $processedUserIds);
            });

            // If no exact match, try fuzzy matching (normalize spaces)
            if (!$matchedUser) {
                $matchedUser = $dbTeachers->first(function ($user) use ($sqlName, $processedUserIds) {
                    if (in_array($user->id, $processedUserIds)) {
                        return false;
                    }
                    $dbName = trim($user->name);
                    $normalizedSql = preg_replace('/\s+/', ' ', $sqlName);
                    $normalizedDb = preg_replace('/\s+/', ' ', $dbName);
                    return $normalizedSql === $normalizedDb;
                });
            }

            // If still no match, try case-insensitive comparison
            if (!$matchedUser) {
                $matchedUser = $dbTeachers->first(function ($user) use ($sqlName, $processedUserIds) {
                    return !in_array($user->id, $processedUserIds) 
                        && mb_strtolower(trim($user->name)) === mb_strtolower($sqlName);
                });
            }

            if ($matchedUser) {
                $matched++;
                $processedUserIds[] = $matchedUser->id;
                
                // Update if whatsapp is different or empty, or country is not EG
                $needsUpdate = false;
                if ($matchedUser->whatsapp_number !== $whatsapp) {
                    $needsUpdate = true;
                }
                if ($matchedUser->country !== 'EG') {
                    $needsUpdate = true;
                }
                
                if ($needsUpdate) {
                    $oldWhatsapp = $matchedUser->whatsapp_number ?? '(empty)';
                    $matchedUser->whatsapp_number = $whatsapp;
                    $matchedUser->country = 'EG'; // Set country to Egypt
                    $matchedUser->save();
                    $updated++;
                    $this->command->info("✓ Updated: {$matchedUser->name} ({$oldWhatsapp} → {$whatsapp}) [Country: EG]");
                } else {
                    $this->command->info("  Already set: {$matchedUser->name} ({$whatsapp})");
                }
            } else {
                $notMatched[] = [
                    'name' => $sqlName,
                    'whatsapp' => $whatsapp,
                ];
            }
        }

        // Second pass: partial matches for remaining SQL entries (only if DB teacher has no WhatsApp)
        $remainingNotMatched = [];
        foreach ($notMatched as $index => $sqlTeacher) {
            $sqlName = trim($sqlTeacher['name']);
            $whatsapp = $this->normalizeWhatsApp($sqlTeacher['whatsapp']);
            $sqlNameLower = mb_strtolower($sqlName);
            $sqlNameParts = explode(' ', $sqlNameLower);
            $firstPart = $sqlNameParts[0] ?? '';
            
            // Only try partial match if first part is meaningful (at least 4 characters for Arabic names)
            if (strlen($firstPart) < 4) {
                $remainingNotMatched[] = $sqlTeacher;
                continue;
            }
            
            $matchedUser = $dbTeachers->first(function ($user) use ($sqlNameLower, $firstPart, $processedUserIds) {
                if (in_array($user->id, $processedUserIds)) {
                    return false;
                }
                
                // Only match if the DB teacher has no WhatsApp number
                if (!empty($user->whatsapp_number)) {
                    return false;
                }
                
                $dbNameLower = mb_strtolower(trim($user->name));
                
                // If SQL name starts with DB name or DB name starts with SQL name
                if (str_starts_with($sqlNameLower, $dbNameLower) || str_starts_with($dbNameLower, $sqlNameLower)) {
                    return true;
                }
                
                // If first part matches
                if (str_starts_with($dbNameLower, $firstPart)) {
                    return true;
                }
                
                return false;
            });

            if ($matchedUser) {
                $matched++;
                $processedUserIds[] = $matchedUser->id;
                $oldWhatsapp = $matchedUser->whatsapp_number ?? '(empty)';
                $matchedUser->whatsapp_number = $whatsapp;
                $matchedUser->country = 'EG'; // Set country to Egypt
                $matchedUser->save();
                $updated++;
                $this->command->info("✓ Updated (partial match): {$matchedUser->name} ({$oldWhatsapp} → {$whatsapp}) [matched with: {$sqlName}] [Country: EG]");
            } else {
                $remainingNotMatched[] = $sqlTeacher;
            }
        }
        
        $notMatched = $remainingNotMatched;

        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('Summary');
        $this->command->info('========================================');
        $this->command->info("Total teachers in SQL: " . count($teachers));
        $this->command->info("Matched: {$matched}");
        $this->command->info("Updated: {$updated}");
        $this->command->info("Not matched: " . count($notMatched));
        $this->command->newLine();

        if (count($notMatched) > 0) {
            $this->command->warn('Teachers not found in database:');
            foreach ($notMatched as $teacher) {
                $this->command->info("  - {$teacher['name']} ({$teacher['whatsapp']})");
            }
            $this->command->newLine();
        }

        $this->command->info('✅ WhatsApp update completed!');
    }

    /**
     * Extract teachers data from SQL file
     */
    private function extractTeachersFromSql(string $filePath): array
    {
        $content = File::get($filePath);
        $teachers = [];

        // Pattern to match INSERT statements
        // Matches: (id, 'name', 'whatsapp', 'created_at', 'updated_at')
        $pattern = '/\((\d+),\s*\'([^\']+)\',\s*\'([^\']+)\',\s*\'([^\']*)\',\s*\'([^\']*)\'\)/';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $teachers[] = [
                    'id' => (int)$match[1],
                    'name' => $this->unescapeSqlString($match[2]),
                    'whatsapp' => $this->unescapeSqlString($match[3]),
                    'created_at' => $match[4] ?: null,
                    'updated_at' => $match[5] ?: null,
                ];
            }
        }

        return $teachers;
    }

    /**
     * Unescape SQL string (handle escaped quotes)
     */
    private function unescapeSqlString(string $str): string
    {
        return str_replace("''", "'", $str);
    }

    /**
     * Normalize WhatsApp number format to Egypt (+20)
     */
    private function normalizeWhatsApp(string $whatsapp): string
    {
        // Remove all spaces and non-digit characters except +
        $whatsapp = preg_replace('/[^\d+]/', '', $whatsapp);
        
        // If it starts with +, check if it's already +20
        if (str_starts_with($whatsapp, '+')) {
            // If it's already +20 (Egypt), return as is
            if (str_starts_with($whatsapp, '+20')) {
                return $whatsapp;
            }
            
            // If it's +1 (US), try to fix it
            if (str_starts_with($whatsapp, '+1')) {
                $number = substr($whatsapp, 2);
                // If the number itself starts with 20, it was a mistake - fix it
                if (str_starts_with($number, '20')) {
                    return '+' . $number;
                }
                // If it's 10 digits starting with 1, it's likely Egyptian
                if (preg_match('/^1\d{9}$/', $number)) {
                    return '+20' . $number;
                }
            }
            
            // For other country codes, extract the number and check
            $number = substr($whatsapp, 1);
            if (str_starts_with($number, '20')) {
                return '+' . $number;
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
                // If it's 9 digits starting with 1, it's Egyptian mobile
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
        }

        return $whatsapp;
    }
}

