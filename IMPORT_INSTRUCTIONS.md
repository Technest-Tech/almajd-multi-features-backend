# Import Reminders Data Instructions

## Overview
This seeder imports data from the old `u835993064_reminders.sql` file into the new database structure.

## What it does:
1. **Extracts teachers** from the old `teachers` table and creates/updates them in the `users` table
2. **Extracts unique student names** from `teachers_time_tables` and creates entries in `calendar_students` table
3. **Creates placeholder users** for each calendar student (required for foreign key constraints)
4. **Groups timetable records** by teacher, student, time, and country
5. **Creates timetables** with proper structure (days_of_week as array)
6. **Generates timetable events** for each timetable

## Setup

1. **Place the SQL file** in one of these locations:
   - `storage/app/imports/u835993064_reminders.sql`
   - `u835993064_reminders.sql` (project root)
   - `/Users/ahmedomar/Downloads/u835993064_reminders.sql`

2. **Run the seeder**:
   ```bash
   php artisan db:seed --class=ImportRemindersDataSeeder
   ```

## Notes

- The seeder creates placeholder users for calendar students because the current `timetables` table structure requires `student_id` to reference the `users` table
- Teachers are created/updated in the `users` table with `user_type = 'teacher'`
- Calendar students are stored in the `calendar_students` table
- Timetables are grouped by: teacher, student, start_time, finish_time, and country
- Days of the week are converted from names (Monday, Tuesday, etc.) to numbers (1-7)
- Countries are mapped: `canada` → `Canada`, `uk` → `United Kingdom`, `eg` → `Egypt`
- Only active timetables (status = 'active' and no deleted_date) are imported

## Troubleshooting

If you encounter parsing errors:
1. Check that the SQL file is complete and not corrupted
2. Verify the file encoding (should be UTF-8)
3. Check the Laravel logs: `storage/logs/laravel.log`














