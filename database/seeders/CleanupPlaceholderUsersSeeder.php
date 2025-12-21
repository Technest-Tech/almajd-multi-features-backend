<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\CalendarStudent;
use App\Models\User;
use Illuminate\Database\Seeder;

class CleanupPlaceholderUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder removes placeholder users that were incorrectly created
     * for calendar students. Calendar students should be isolated from users table.
     */
    public function run(): void
    {
        $this->command->info('Cleaning up placeholder users for calendar students...');
        
        // Get all calendar student names
        $calendarStudentNames = CalendarStudent::pluck('name')->toArray();
        
        if (empty($calendarStudentNames)) {
            $this->command->info('No calendar students found. Nothing to clean up.');
            return;
        }
        
        // Find users that match calendar student names and are marked as students
        $placeholderUsers = User::where('user_type', UserType::Student)
            ->whereIn('name', $calendarStudentNames)
            ->get();
        
        $count = $placeholderUsers->count();
        
        if ($count === 0) {
            $this->command->info('No placeholder users found. Nothing to clean up.');
            return;
        }
        
        $this->command->info("Found {$count} placeholder users to remove.");
        
        // Delete the placeholder users
        $deleted = User::where('user_type', UserType::Student)
            ->whereIn('name', $calendarStudentNames)
            ->delete();
        
        $this->command->info("Successfully removed {$deleted} placeholder users.");
        $this->command->info('Calendar students are now properly isolated from users table.');
    }
}













