<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Database\Seeders\Helpers\OldDataAnalyzer;
use Database\Seeders\Helpers\CountryCodeMapper;
use Database\Seeders\Helpers\ArabicTransliterator;

class OldDataMigrationSeeder extends Seeder
{
    private array $log = [];
    private array $existingEmails = [];
    private array $currencyMap = [
        1 => 'USD',
        2 => 'EUR',
        3 => 'CAD',
        4 => 'GBP',
    ];
    
    private array $stats = [
        'teachers_migrated' => 0,
        'admins_migrated' => 0,
        'students_migrated' => 0,
        'relationships_migrated' => 0,
        'errors' => 0,
        'warnings' => 0,
    ];

    private int $teacherIdOffset = 10000;
    private string $dataPath;
    private string $logFile;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->dataPath = database_path('data/old_database');
        $this->logFile = storage_path('logs/migration_' . date('Y-m-d_His') . '.log');
        
        $this->log("=== STARTING OLD DATABASE MIGRATION ===\n");
        $this->log("Start Time: " . now()->toDateTimeString() . "\n");

        try {
            // Phase 0: Analyze data
            $this->analyzeOldData();

            // Phase 1: Setup
            $this->setupMigration();

            // Phase 2: Migrate teachers and admins
            $this->migrateTeachersAndAdmins();

            // Phase 3: Migrate students (families)
            $this->migrateStudents();

            // Phase 4: Migrate teacher-student relationships
            $this->migrateRelationships();

            // Phase 5: Validation
            $this->validateMigration();

            // Generate summary
            $this->generateSummary();

        } catch (\Exception $e) {
            $this->log("CRITICAL ERROR: " . $e->getMessage());
            $this->log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        } finally {
            $this->saveLogFile();
        }
    }

    /**
     * Phase 0: Analyze old data
     */
    private function analyzeOldData(): void
    {
        $this->log("\n=== PHASE 0: ANALYZING OLD DATA ===\n");

        $analyzer = new OldDataAnalyzer();
        $stats = $analyzer->analyze($this->dataPath);
        $report = $analyzer->generateReport();
        
        $this->log($report);
    }

    /**
     * Phase 1: Setup migration
     */
    private function setupMigration(): void
    {
        $this->log("\n=== PHASE 1: SETUP ===\n");

        // Load existing emails to avoid conflicts
        $this->existingEmails = DB::table('users')->pluck('email')->toArray();
        $this->log("Loaded " . count($this->existingEmails) . " existing emails\n");

        // Verify data files exist
        $requiredFiles = ['currencies.sql', 'users.sql', 'families.sql', 'family_tutor.sql'];
        foreach ($requiredFiles as $file) {
            $path = $this->dataPath . '/' . $file;
            if (!file_exists($path)) {
                throw new \Exception("Required file not found: {$file}");
            }
            $this->log("Found: {$file}\n");
        }
    }

    /**
     * Phase 2: Migrate teachers and admins
     */
    private function migrateTeachersAndAdmins(): void
    {
        $this->log("\n=== PHASE 2: MIGRATING TEACHERS AND ADMINS ===\n");

        $content = file_get_contents($this->dataPath . '/users.sql');
        
        // Extract INSERT statements
        preg_match_all(
            '/INSERT INTO `users`[^;]+VALUES\s*(.*?);/s',
            $content,
            $matches
        );

        if (empty($matches[1])) {
            $this->log("WARNING: No user records found\n");
            return;
        }

        $valuesString = $matches[1][0];
        
        // Parse individual records
        preg_match_all(
            '/\((\d+),\s*\'([^\']+)\',\s*\'([^\']+)\',\s*([^,]+),\s*\'([^\']+)\',\s*(\d+),\s*\'([^\']*)\',\s*\'([^\']*)\',\s*([^,]*),\s*([^,]*),\s*([^)]+)\)/s',
            $valuesString,
            $recordMatches,
            PREG_SET_ORDER
        );

        foreach ($recordMatches as $match) {
            $oldId = (int)$match[1];
            $name = $this->cleanString($match[2]);
            $email = $this->cleanString($match[3]);
            $emailVerifiedAt = $this->parseNullableValue($match[4]);
            $password = $this->cleanString($match[5]);
            $userTypeId = (int)$match[6];
            $bankName = $this->cleanString($match[7]);
            $accountNumber = $this->cleanString($match[8]);
            $rememberToken = $this->parseNullableValue($match[9]);
            $createdAt = $this->parseNullableValue($match[10]);
            $updatedAt = $this->parseNullableValue($match[11]);

            try {
                // Determine user type
                $userType = $userTypeId === 0 ? 'admin' : 'teacher';
                
                // Calculate new ID with offset
                $newId = $oldId + $this->teacherIdOffset;

                // Check if user already exists by ID
                $existingUser = DB::table('users')->where('id', $newId)->exists();
                if ($existingUser) {
                    $this->log("INFO: Teacher/Admin ID {$newId} already exists. Skipping.\n");
                    $this->existingEmails[] = $email;
                    continue;
                }

                // Check if email already exists
                if (in_array($email, $this->existingEmails)) {
                    $this->log("WARNING: Email already exists: {$email}. Skipping user ID {$oldId}\n");
                    $this->stats['warnings']++;
                    continue;
                }

                DB::table('users')->insert([
                    'id' => $newId,
                    'name' => $name,
                    'email' => $email,
                    'email_verified_at' => $emailVerifiedAt,
                    'password' => $password,
                    'user_type' => $userType,
                    'whatsapp_number' => null,
                    'country' => null,
                    'currency' => null,
                    'hour_price' => null,
                    'bank_name' => $bankName ?: null,
                    'account_number' => $accountNumber ?: null,
                    'remember_token' => $rememberToken,
                    'created_at' => $createdAt ?: now(),
                    'updated_at' => $updatedAt ?: now(),
                ]);

                $this->existingEmails[] = $email;
                
                if ($userType === 'admin') {
                    $this->stats['admins_migrated']++;
                } else {
                    $this->stats['teachers_migrated']++;
                }

                $this->log("Migrated {$userType}: {$name} (Old ID: {$oldId} → New ID: {$newId})\n");

            } catch (\Exception $e) {
                $this->log("ERROR migrating user {$oldId}: " . $e->getMessage() . "\n");
                $this->stats['errors']++;
            }
        }

        $this->log("\nTeachers migrated: {$this->stats['teachers_migrated']}\n");
        $this->log("Admins migrated: {$this->stats['admins_migrated']}\n");
    }

    /**
     * Phase 3: Migrate students (families)
     */
    private function migrateStudents(): void
    {
        $this->log("\n=== PHASE 3: MIGRATING STUDENTS (FAMILIES) ===\n");

        $content = file_get_contents($this->dataPath . '/families.sql');
        
        // Extract ALL INSERT statements (there may be multiple)
        preg_match_all(
            '/INSERT INTO `families`[^;]+VALUES\s*(.*?);/s',
            $content,
            $matches
        );

        if (empty($matches[1])) {
            $this->log("WARNING: No family records found\n");
            return;
        }

        $batchSize = 100;
        $batch = [];
        $count = 0;
        $totalProcessed = 0;

        // Process each INSERT statement
        foreach ($matches[1] as $insertIndex => $valuesString) {
            $this->log("Processing INSERT statement " . ($insertIndex + 1) . " of " . count($matches[1]) . "...\n");
            
            // Parse individual records from this INSERT statement
            preg_match_all(
                '/\((\d+),\s*\'([^\']+)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*([^,]+),\s*(\d+),\s*\'([^\']+)\',\s*([^)]+)\)/s',
                $valuesString,
                $recordMatches,
                PREG_SET_ORDER
            );

            $totalProcessed += count($recordMatches);
            $this->log("Found " . count($recordMatches) . " records in this INSERT statement (Total so far: {$totalProcessed})\n");

            foreach ($recordMatches as $match) {
                $id = (int)$match[1];
                $name = $this->cleanString($match[2]);
                $whatsappNumber = $this->cleanString($match[3]);
                $countryCode = $this->cleanString($match[4]);
                $hourPrice = (float)$match[5];
                $currencyId = (int)$match[6];
                $createdAt = $this->parseTimestamp($match[7]);
                $updatedAt = $this->parseNullableValue($match[8]);

                try {
                    // Check if student already exists
                    $existingStudent = DB::table('users')->where('id', $id)->exists();
                    if ($existingStudent) {
                        $count++;
                        $this->stats['students_migrated']++;
                        
                        // Still need to track the email
                        $email = ArabicTransliterator::generateEmail($id, $name);
                        $this->existingEmails[] = $email;
                        
                        // Log every 100 existing students
                        if ($count % 100 === 0) {
                            $this->log("Skipped {$count} existing students\n");
                        }
                        continue;
                    }

                    // Generate email
                    $email = ArabicTransliterator::generateEmail($id, $name);
                    $email = ArabicTransliterator::ensureUniqueEmail($email, $this->existingEmails);

                    // Map currency
                    $currency = $this->currencyMap[$currencyId] ?? 'USD';

                    // Extract country from phone number
                    $country = null;
                    if (!empty($whatsappNumber)) {
                        $country = CountryCodeMapper::extractCountryCode($whatsappNumber);
                    }

                    // Sanitize name
                    $sanitizedName = ArabicTransliterator::sanitizeName($name);

                    $batch[] = [
                        'id' => $id,
                        'name' => $sanitizedName,
                        'email' => $email,
                        'email_verified_at' => null,
                        'password' => Hash::make('Student@123'),
                        'user_type' => 'student',
                        'whatsapp_number' => $whatsappNumber ?: null,
                        'country' => $country,
                        'currency' => $currency,
                        'hour_price' => $hourPrice,
                        'bank_name' => null,
                        'account_number' => null,
                        'remember_token' => null,
                        'created_at' => $createdAt ?: now(),
                        'updated_at' => $updatedAt ?: now(),
                    ];

                    $this->existingEmails[] = $email;
                    $count++;

                    // Insert in batches
                    if (count($batch) >= $batchSize) {
                        DB::table('users')->insert($batch);
                        $this->stats['students_migrated'] += count($batch);
                        $this->log("Inserted batch of " . count($batch) . " students (Total: {$count})\n");
                        $batch = [];
                    }

                } catch (\Exception $e) {
                    $this->log("ERROR migrating student {$id}: " . $e->getMessage() . "\n");
                    $this->stats['errors']++;
                }
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            DB::table('users')->insert($batch);
            $this->stats['students_migrated'] += count($batch);
            $this->log("Inserted final batch of " . count($batch) . " students\n");
        }

        $this->log("\nTotal students migrated: {$this->stats['students_migrated']}\n");
        $this->log("Total students processed: {$totalProcessed}\n");
    }

    /**
     * Phase 4: Migrate teacher-student relationships
     */
    private function migrateRelationships(): void
    {
        $this->log("\n=== PHASE 4: MIGRATING TEACHER-STUDENT RELATIONSHIPS ===\n");

        $content = file_get_contents($this->dataPath . '/family_tutor.sql');
        
        // Extract ALL INSERT statements (there may be multiple)
        preg_match_all(
            '/INSERT INTO `family_tutor`[^;]+VALUES\s*(.*?);/s',
            $content,
            $matches
        );

        if (empty($matches[1])) {
            $this->log("WARNING: No family_tutor records found\n");
            return;
        }

        $batchSize = 500;
        $batch = [];
        $count = 0;
        $skipped = 0;
        $seenPairs = []; // Track seen teacher-student pairs to avoid duplicates
        $totalProcessed = 0;

        // Process each INSERT statement
        foreach ($matches[1] as $insertIndex => $valuesString) {
            $this->log("Processing INSERT statement " . ($insertIndex + 1) . " of " . count($matches[1]) . "...\n");
            
            // Parse individual records from this INSERT statement
            preg_match_all(
                '/\((\d+),\s*(\d+),\s*(\d+),\s*([^,]*),\s*([^)]+)\)/s',
                $valuesString,
                $recordMatches,
                PREG_SET_ORDER
            );

            $totalProcessed += count($recordMatches);
            $this->log("Found " . count($recordMatches) . " records in this INSERT statement (Total so far: {$totalProcessed})\n");

            foreach ($recordMatches as $match) {
                $oldTeacherId = (int)$match[2];
                $studentId = (int)$match[3];
                $createdAt = $this->parseNullableValue($match[4]);
                $updatedAt = $this->parseNullableValue($match[5]);

                try {
                    // Calculate new teacher ID with offset
                    $newTeacherId = $oldTeacherId + $this->teacherIdOffset;

                    // Create unique key for this relationship
                    $pairKey = "{$newTeacherId}-{$studentId}";

                    // Check if we've already seen this pair in current processing
                    if (isset($seenPairs[$pairKey])) {
                        $skipped++;
                        continue;
                    }

                    // Verify both users exist
                    $teacherExists = DB::table('users')->where('id', $newTeacherId)->exists();
                    $studentExists = DB::table('users')->where('id', $studentId)->exists();

                    if (!$teacherExists || !$studentExists) {
                        $skipped++;
                        if (!$teacherExists) {
                            $this->log("WARNING: Teacher {$newTeacherId} not found. Skipping relationship.\n");
                        }
                        if (!$studentExists) {
                            $this->log("WARNING: Student {$studentId} not found. Skipping relationship.\n");
                        }
                        continue;
                    }

                    // Check if relationship already exists in database
                    $exists = DB::table('teacher_student')
                        ->where('teacher_id', $newTeacherId)
                        ->where('student_id', $studentId)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        $seenPairs[$pairKey] = true;
                        continue;
                    }

                    // Mark as seen
                    $seenPairs[$pairKey] = true;

                    $batch[] = [
                        'teacher_id' => $newTeacherId,
                        'student_id' => $studentId,
                        'created_at' => $createdAt ?: now(),
                        'updated_at' => $updatedAt ?: now(),
                    ];

                    $count++;

                    // Insert in batches
                    if (count($batch) >= $batchSize) {
                        DB::table('teacher_student')->insert($batch);
                        $this->stats['relationships_migrated'] += count($batch);
                        $this->log("Inserted batch of " . count($batch) . " relationships (Total: {$count})\n");
                        $batch = [];
                    }

                } catch (\Exception $e) {
                    $this->log("ERROR migrating relationship (Teacher: {$oldTeacherId}, Student: {$studentId}): " . $e->getMessage() . "\n");
                    $this->stats['errors']++;
                }
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            DB::table('teacher_student')->insert($batch);
            $this->stats['relationships_migrated'] += count($batch);
            $this->log("Inserted final batch of " . count($batch) . " relationships\n");
        }

        $this->log("\nTotal relationships migrated: {$this->stats['relationships_migrated']}\n");
        $this->log("Total relationships processed: {$totalProcessed}\n");
        $this->log("Skipped relationships: {$skipped}\n");
    }

    /**
     * Phase 5: Validate migration
     */
    private function validateMigration(): void
    {
        $this->log("\n=== PHASE 5: VALIDATION ===\n");

        // Check for duplicate emails
        $duplicates = DB::table('users')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->count() > 0) {
            $this->log("ERROR: Found {$duplicates->count()} duplicate emails:\n");
            foreach ($duplicates as $dup) {
                $this->log("  - {$dup->email} ({$dup->count} times)\n");
            }
            $this->stats['errors'] += $duplicates->count();
        } else {
            $this->log("✓ No duplicate emails found\n");
        }

        // Verify user counts
        $totalUsers = DB::table('users')->count();
        $admins = DB::table('users')->where('user_type', 'admin')->count();
        $teachers = DB::table('users')->where('user_type', 'teacher')->count();
        $students = DB::table('users')->where('user_type', 'student')->count();

        $this->log("\nUser counts:\n");
        $this->log("  Total users: {$totalUsers}\n");
        $this->log("  Admins: {$admins}\n");
        $this->log("  Teachers: {$teachers}\n");
        $this->log("  Students: {$students}\n");

        // Verify relationships
        $totalRelationships = DB::table('teacher_student')->count();
        $this->log("  Total relationships: {$totalRelationships}\n");

        // Check for orphaned relationships
        $orphanedTeachers = DB::table('teacher_student')
            ->leftJoin('users as t', 'teacher_student.teacher_id', '=', 't.id')
            ->whereNull('t.id')
            ->count();

        $orphanedStudents = DB::table('teacher_student')
            ->leftJoin('users as s', 'teacher_student.student_id', '=', 's.id')
            ->whereNull('s.id')
            ->count();

        if ($orphanedTeachers > 0 || $orphanedStudents > 0) {
            $this->log("\nWARNING: Found orphaned relationships:\n");
            $this->log("  Orphaned teachers: {$orphanedTeachers}\n");
            $this->log("  Orphaned students: {$orphanedStudents}\n");
            $this->stats['warnings'] += $orphanedTeachers + $orphanedStudents;
        } else {
            $this->log("\n✓ No orphaned relationships found\n");
        }
    }

    /**
     * Generate migration summary
     */
    private function generateSummary(): void
    {
        $this->log("\n=== MIGRATION SUMMARY ===\n");
        $this->log("Admins migrated: {$this->stats['admins_migrated']}\n");
        $this->log("Teachers migrated: {$this->stats['teachers_migrated']}\n");
        $this->log("Students migrated: {$this->stats['students_migrated']}\n");
        $this->log("Relationships migrated: {$this->stats['relationships_migrated']}\n");
        $this->log("Errors: {$this->stats['errors']}\n");
        $this->log("Warnings: {$this->stats['warnings']}\n");
        $this->log("\nEnd Time: " . now()->toDateTimeString() . "\n");
        $this->log("=== MIGRATION COMPLETE ===\n");

        // Also output to console
        $this->command->info("Migration completed!");
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Admins', $this->stats['admins_migrated']],
                ['Teachers', $this->stats['teachers_migrated']],
                ['Students', $this->stats['students_migrated']],
                ['Relationships', $this->stats['relationships_migrated']],
                ['Errors', $this->stats['errors']],
                ['Warnings', $this->stats['warnings']],
            ]
        );
        $this->command->info("Log file: {$this->logFile}");
    }

    /**
     * Helper: Clean string from SQL
     */
    private function cleanString(string $value): string
    {
        // Remove surrounding quotes
        $value = trim($value, "'\"");
        
        // Unescape SQL escapes
        $value = str_replace("\\'", "'", $value);
        $value = str_replace("\\\"", "\"", $value);
        $value = str_replace("\\\\", "\\", $value);
        
        return $value;
    }

    /**
     * Helper: Parse nullable values
     */
    private function parseNullableValue($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        $value = trim($value);
        
        if ($value === 'NULL' || $value === '') {
            return null;
        }
        
        return $this->cleanString($value);
    }

    /**
     * Helper: Parse timestamp
     */
    private function parseTimestamp($value)
    {
        $cleaned = $this->parseNullableValue($value);
        
        if ($cleaned === null) {
            return null;
        }
        
        try {
            return \Carbon\Carbon::parse($cleaned);
        } catch (\Exception $e) {
            return now();
        }
    }

    /**
     * Log message
     */
    private function log(string $message): void
    {
        $this->log[] = $message;
        echo $message;
    }

    /**
     * Save log to file
     */
    private function saveLogFile(): void
    {
        $logContent = implode('', $this->log);
        file_put_contents($this->logFile, $logContent);
    }
}

