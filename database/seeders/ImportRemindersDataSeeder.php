<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\CalendarStudent;
use App\Models\Timetable;
use App\Models\TimetableEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportRemindersDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Try multiple possible locations
        $possiblePaths = [
            storage_path('app/imports/u835993064_reminders.sql'),
            base_path('u835993064_reminders.sql'),
            '/Users/ahmedomar/Downloads/u835993064_reminders.sql',
        ];
        
        $sqlFile = null;
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $sqlFile = $path;
                break;
            }
        }
        
        if (!$sqlFile) {
            $this->command->error("SQL file not found. Tried:");
            foreach ($possiblePaths as $path) {
                $this->command->info("  - {$path}");
            }
            return;
        }
        
        $this->command->info("Using SQL file: {$sqlFile}");

        $this->command->info('Starting data import...');
        
        // Read SQL file
        $sqlContent = File::get($sqlFile);
        
        // Extract teachers data
        $teachers = $this->extractTeachers($sqlContent);
        $this->command->info("Found " . count($teachers) . " teachers");
        
        // Extract timetable data
        $timeTables = $this->extractTimeTables($sqlContent);
        $this->command->info("Found " . count($timeTables) . " timetable records");
        
        // Create/update teachers in users table
        $teacherMap = $this->createTeachers($teachers);
        $this->command->info("Created/updated " . count($teacherMap) . " teachers in users table");
        
        // Extract unique student names and create calendar_students
        $studentNames = $this->extractUniqueStudentNames($timeTables);
        $calendarStudentMap = $this->createCalendarStudents($studentNames);
        $this->command->info("Created " . count($calendarStudentMap) . " calendar students");
        
        // Group timetable records and create timetables
        $timetableGroups = $this->groupTimeTables($timeTables);
        $this->command->info("Grouped into " . count($timetableGroups) . " timetables");
        
        // Create timetables and events
        $this->createTimetablesAndEvents($timetableGroups, $teacherMap, $calendarStudentMap);
        $this->command->info("Import completed successfully!");
    }

    /**
     * Extract teachers from SQL content
     */
    private function extractTeachers(string $sqlContent): array
    {
        $teachers = [];
        
        // Find the INSERT statement for teachers
        if (preg_match('/INSERT INTO `teachers`[^;]+VALUES\s*(.+?);/is', $sqlContent, $matches)) {
            $valuesBlock = $matches[1];
            
            // Split by ), to get individual rows
            $rows = preg_split('/\),\s*/', $valuesBlock);
            
            foreach ($rows as $row) {
                $row = trim($row);
                // Remove leading ( and trailing )
                $row = preg_replace('/^\(|\)$/', '', $row);
                
                if (empty($row)) continue;
                
                // Parse the row - handle escaped quotes
                if (preg_match('/^(\d+),\s*\'(.*?)\',\s*\'(.*?)\',\s*\'(.*?)\',\s*\'(.*?)\'$/', $row, $m)) {
                    $teachers[] = [
                        'id' => (int)$m[1],
                        'name' => str_replace("\\'", "'", $m[2]),
                        'whatsapp' => str_replace("\\'", "'", $m[3]),
                        'created_at' => str_replace("\\'", "'", $m[4]),
                        'updated_at' => str_replace("\\'", "'", $m[5]),
                    ];
                }
            }
        }
        
        return $teachers;
    }

    /**
     * Extract timetable records from SQL content
     */
    private function extractTimeTables(string $sqlContent): array
    {
        $timeTables = [];
        
        // Match all INSERT statements for teachers_time_tables
        $pattern = '/INSERT INTO `teachers_time_tables`[^;]+VALUES\s*(.+?);/is';
        preg_match_all($pattern, $sqlContent, $matches);
        
        foreach ($matches[1] as $valuesBlock) {
            // Split by lines and process each line
            $lines = explode("\n", $valuesBlock);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Remove trailing comma if present
                $line = rtrim($line, ',');
                
                // Match the pattern: (id, teacher_id, 'day', 'start_time', 'finish_time', 'student_name', 'country', 'created_at', 'updated_at', 'status', 'reactive_date', 'deleted_date')
                // Handle NULL values
                $pattern = '/\((\d+),\s*(\d+),\s*\'([^\']*)\',\s*\'([^\']*)\',\s*(NULL|\'[^\']*\'|\'\'),\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*(NULL|\'[^\']*\'|\'\'),\s*(NULL|\'[^\']*\'|\'\')\)/';
                
                if (preg_match($pattern, $line, $m)) {
                    $finishTime = ($m[5] === 'NULL' || $m[5] === "''") ? null : trim($m[5], "'");
                    $reactiveDate = ($m[11] === 'NULL' || $m[11] === "''") ? null : trim($m[11], "'");
                    $deletedDate = ($m[12] === 'NULL' || $m[12] === "''") ? null : trim($m[12], "'");
                    
                    $timeTables[] = [
                        'id' => (int)$m[1],
                        'teacher_id' => (int)$m[2],
                        'day' => str_replace("\\'", "'", $m[3]),
                        'start_time' => str_replace("\\'", "'", $m[4]),
                        'finish_time' => $finishTime,
                        'student_name' => str_replace("\\'", "'", $m[6]),
                        'country' => str_replace("\\'", "'", $m[7]),
                        'created_at' => str_replace("\\'", "'", $m[8]),
                        'updated_at' => str_replace("\\'", "'", $m[9]),
                        'status' => str_replace("\\'", "'", $m[10]) ?: 'active',
                        'reactive_date' => $reactiveDate,
                        'deleted_date' => $deletedDate,
                    ];
                }
            }
        }
        
        return $timeTables;
    }

    /**
     * Create or update teachers in users table
     */
    private function createTeachers(array $teachers): array
    {
        $teacherMap = [];
        
        foreach ($teachers as $teacher) {
            // Check if teacher already exists by name
            $user = User::where('name', $teacher['name'])
                ->where('user_type', UserType::Teacher)
                ->first();
            
            if (!$user) {
                // Create new teacher user
                $user = User::create([
                    'name' => $teacher['name'],
                    'email' => $this->generateEmail($teacher['name']),
                    'password' => bcrypt('password'), // Default password
                    'user_type' => UserType::Teacher,
                    'whatsapp_number' => $teacher['whatsapp'],
                    'created_at' => $teacher['created_at'],
                    'updated_at' => $teacher['updated_at'],
                ]);
            } else {
                // Update whatsapp if not set
                if (!$user->whatsapp_number && $teacher['whatsapp']) {
                    $user->whatsapp_number = $teacher['whatsapp'];
                    $user->save();
                }
            }
            
            $teacherMap[$teacher['id']] = $user->id;
        }
        
        return $teacherMap;
    }

    /**
     * Extract unique student names
     */
    private function extractUniqueStudentNames(array $timeTables): array
    {
        $studentNames = [];
        
        foreach ($timeTables as $tt) {
            if (!empty($tt['student_name']) && !in_array($tt['student_name'], $studentNames)) {
                $studentNames[] = $tt['student_name'];
            }
        }
        
        return $studentNames;
    }

    /**
     * Create calendar students (isolated from users table)
     */
    private function createCalendarStudents(array $studentNames): array
    {
        $calendarStudentMap = [];
        
        foreach ($studentNames as $studentName) {
            // Create calendar student (isolated from users table)
            $calendarStudent = CalendarStudent::firstOrCreate(
                ['name' => $studentName],
                ['name' => $studentName]
            );
            
            $calendarStudentMap[$studentName] = $calendarStudent->id;
        }
        
        return $calendarStudentMap;
    }

    /**
     * Group timetable records by teacher, student, time, and country
     */
    private function groupTimeTables(array $timeTables): array
    {
        $groups = [];
        
        foreach ($timeTables as $tt) {
            // Skip deleted or inactive records
            if ($tt['status'] !== 'active' || $tt['deleted_date']) {
                continue;
            }
            
            // Create a unique key for grouping
            $key = sprintf(
                '%d_%s_%s_%s_%s',
                $tt['teacher_id'],
                $tt['student_name'],
                $tt['start_time'],
                $tt['finish_time'] ?: '',
                $tt['country']
            );
            
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'teacher_id' => $tt['teacher_id'],
                    'student_name' => $tt['student_name'],
                    'start_time' => $tt['start_time'],
                    'finish_time' => $tt['finish_time'],
                    'country' => $tt['country'],
                    'days' => [],
                    'created_at' => $tt['created_at'],
                    'updated_at' => $tt['updated_at'],
                ];
            }
            
            // Add day to the group
            $dayNumber = $this->dayNameToNumber($tt['day']);
            if ($dayNumber && !in_array($dayNumber, $groups[$key]['days'])) {
                $groups[$key]['days'][] = $dayNumber;
            }
        }
        
        return array_values($groups);
    }

    /**
     * Convert day name to number (1=Monday, 7=Sunday)
     */
    private function dayNameToNumber(string $dayName): ?int
    {
        $days = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7,
        ];
        
        return $days[$dayName] ?? null;
    }

    /**
     * Convert country code to timezone name
     */
    private function countryToTimezone(string $country): string
    {
        $mapping = [
            'canada' => 'Canada',
            'uk' => 'United Kingdom',
            'eg' => 'Egypt',
        ];
        
        return $mapping[strtolower($country)] ?? 'Canada';
    }

    /**
     * Create timetables and events
     */
    private function createTimetablesAndEvents(array $timetableGroups, array $teacherMap, array $calendarStudentMap): void
    {
        DB::beginTransaction();
        
        try {
            foreach ($timetableGroups as $group) {
                // Skip if no days
                if (empty($group['days'])) {
                    continue;
                }
                
                // Get teacher user ID
                $teacherUserId = $teacherMap[$group['teacher_id']] ?? null;
                if (!$teacherUserId) {
                    continue;
                }
                
                // Get calendar student ID (isolated from users table)
                $calendarStudentId = $calendarStudentMap[$group['student_name']] ?? null;
                if (!$calendarStudentId) {
                    continue;
                }
                
                // Calculate start_date and end_date (use created_at as start, or current date)
                $startDate = $group['created_at'] 
                    ? Carbon::parse($group['created_at'])->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d');
                
                $endDate = Carbon::parse($startDate)->addYear()->format('Y-m-d');
                
                // Create timetable with calendar_student_id (isolated from users table)
                $timetable = Timetable::create([
                    'calendar_student_id' => $calendarStudentId, // Use calendar student, not user
                    'student_id' => null, // Keep null - calendar students are isolated
                    'teacher_id' => $teacherUserId,
                    'course_name' => $group['student_name'], // Use student name as course name
                    'timezone' => $this->countryToTimezone($group['country']),
                    'start_time' => $group['start_time'],
                    'end_time' => $group['finish_time'] ?: $group['start_time'],
                    'days_of_week' => $group['days'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'created_by' => $teacherUserId,
                    'is_active' => true,
                    'created_at' => $group['created_at'],
                    'updated_at' => $group['updated_at'],
                ]);
                
                // Generate events for the timetable
                $this->generateTimetableEvents($timetable, $group);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error creating timetables: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate events for a timetable
     */
    private function generateTimetableEvents(Timetable $timetable, array $group): void
    {
        $startDate = Carbon::parse($timetable->start_date);
        $endDate = Carbon::parse($timetable->end_date);
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0=Sunday, 6=Saturday
            // Convert to our format (1=Monday, 7=Sunday)
            $dayNumber = $dayOfWeek === 0 ? 7 : $dayOfWeek;
            
            if (in_array($dayNumber, $timetable->days_of_week)) {
                TimetableEvent::create([
                    'timetable_id' => $timetable->id,
                    'event_date' => $currentDate->format('Y-m-d'),
                    'start_time' => $timetable->start_time,
                    'end_time' => $timetable->end_time,
                    'teacher_id' => $timetable->teacher_id,
                    'course_name' => $timetable->course_name,
                    'status' => 'scheduled',
                    'created_at' => $timetable->created_at,
                    'updated_at' => $timetable->updated_at,
                ]);
            }
            
            $currentDate->addDay();
        }
    }

    /**
     * Generate email from name
     */
    private function generateEmail(string $name): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $email = $base . '@example.com';
        
        // Ensure uniqueness
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = $base . $counter . '@example.com';
            $counter++;
        }
        
        return $email;
    }
}
