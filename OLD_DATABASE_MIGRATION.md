# Old Database Migration Guide

## Overview
This guide explains how to migrate data from the old database system into the new unified system.

## Prerequisites
1. Ensure all 4 SQL dump files are placed in `database/data/old_database/`:
   - `currencies.sql`
   - `users.sql`
   - `families.sql`
   - `family_tutor.sql`

2. **IMPORTANT**: Backup your current database before running migration:
   ```bash
   php artisan db:dump
   # or manually backup using your database client
   ```

## Migration Process

### Step 1: Review the Data
Before migrating, you can analyze the old data to see what will be imported:

```bash
php artisan db:seed --class=OldDataMigrationSeeder --dry-run
```

### Step 2: Run the Migration
Execute the migration seeder:

```bash
php artisan db:seed --class=OldDataMigrationSeeder
```

### Step 3: Review the Log
Check the migration log for any warnings or errors:

```bash
cat storage/logs/migration_*.log
```

## What Gets Migrated

### Teachers & Admins (from `users` table)
- **ID Mapping**: Old ID + 10000 → New ID (e.g., 48 → 10048)
- **User Type**: 
  - user_type_id = 0 → admin
  - user_type_id = 1 → teacher
- **Preserved**: name, email, password, bank_name, account_number, timestamps
- **New Fields**: Set to NULL (whatsapp_number, country, currency, hour_price)

### Students (from `families` table)
- **ID Mapping**: Original family ID preserved (e.g., 38 → 38)
- **Email Generation**: `student_{id}_{transliterated_name}@almajd.com`
  - Example: Family ID 38 "سلام وسارة" → `student_38_salam_wsara@almajd.com`
- **Password**: Default password `Student@123` (hashed)
- **Currency**: Mapped from currency_id (1=USD, 2=EUR, 3=CAD, 4=GBP)
- **Country**: Auto-extracted from phone number country code
- **Preserved**: name, whatsapp_number, hour_price, timestamps

### Teacher-Student Relationships (from `family_tutor` table)
- Maps to `teacher_student` pivot table
- **Teacher ID**: Old user_id + 10000 → teacher_id
- **Student ID**: Original family_id → student_id
- **Example**: (user_id=48, family_id=37) → (teacher_id=10048, student_id=37)

## Key Features

### Smart Email Generation
- Transliterates Arabic names to Latin characters
- Ensures uniqueness by appending `_2`, `_3`, etc. if conflicts exist
- Example: "محمد أحمد" → `student_123_mhmd_ahmd@almajd.com`

### Country Detection
- Automatically extracts country from phone numbers
- Handles North America (+1) disambiguation between US/Canada
- Examples:
  - +17807080708 → CA (Canada)
  - +447899493999 → GB (United Kingdom)
  - +966505791773 → SA (Saudi Arabia)

### Data Validation
- Checks for duplicate emails
- Verifies all foreign key relationships
- Detects orphaned records
- Logs all warnings and errors

### Comprehensive Logging
- Detailed log file created for each migration run
- Stored in `storage/logs/migration_YYYY-MM-DD_HHMMSS.log`
- Includes:
  - Data analysis report
  - Record-by-record migration status
  - Warnings and errors
  - Final summary statistics

## Migration Statistics

After migration, you'll see a summary table:

| Metric | Count |
|--------|-------|
| Admins | X |
| Teachers | X |
| Students | X |
| Relationships | X |
| Errors | X |
| Warnings | X |

## Common Issues & Solutions

### Issue: Email Conflicts
**Symptom**: Warning "Email already exists"
**Solution**: The seeder automatically skips conflicting teachers. Review the log to identify which users were skipped.

### Issue: Orphaned Relationships
**Symptom**: Warning "Teacher/Student not found"
**Solution**: Some family_tutor records reference non-existent users or families. These are automatically skipped and logged.

### Issue: Invalid Currency IDs
**Symptom**: Students with unexpected currency
**Solution**: Any currency_id not in [1,2,3,4] defaults to USD. Check the log for affected records.

### Issue: Country Not Detected
**Symptom**: Country field is NULL for some students
**Solution**: Phone number format couldn't be parsed. Manually review and update these records.

## Rollback Plan

If you need to rollback the migration:

1. Restore your database backup
2. Or manually delete migrated records:

```sql
-- Delete teacher-student relationships
DELETE FROM teacher_student;

-- Delete migrated students (assuming IDs 1-1500)
DELETE FROM users WHERE user_type = 'student' AND id < 2000;

-- Delete migrated teachers (IDs 10000+)
DELETE FROM users WHERE id >= 10000;
```

## Testing Recommendations

### 1. Test on Development Database First
```bash
# Set environment to development
php artisan db:seed --class=OldDataMigrationSeeder --env=development
```

### 2. Verify Sample Records
Manually check a few records to ensure data accuracy:
- Pick a random teacher and verify their information
- Pick a random student and verify email, phone, currency
- Check if teacher-student relationships are correct

### 3. Test Logins
- Try logging in with an old teacher account (use their original email)
- Try logging in with a new student account (use generated email and password `Student@123`)

### 4. Check Application Features
- Verify teacher can see their assigned students
- Check if scheduling/calendar features work with migrated data
- Test billing with different currencies

## Post-Migration Tasks

1. **Notify Users**: 
   - Send teachers their login instructions (email unchanged)
   - Send students their new login credentials (email + default password)
   - Encourage password changes

2. **Manual Review**:
   - Review records with warnings in the log
   - Update any NULL country codes manually
   - Fix any incorrect currency mappings

3. **Data Cleanup**:
   - Consider merging duplicate student records if any
   - Update teacher bank information if needed
   - Verify all phone numbers are correct

## Support

For issues or questions:
1. Check the migration log file
2. Review this guide
3. Contact the development team with the log file attached

## File Locations

- **Seeder**: `database/seeders/OldDataMigrationSeeder.php`
- **Helpers**:
  - `database/seeders/helpers/OldDataAnalyzer.php`
  - `database/seeders/helpers/CountryCodeMapper.php`
  - `database/seeders/helpers/ArabicTransliterator.php`
- **Data Files**: `database/data/old_database/*.sql`
- **Logs**: `storage/logs/migration_*.log`






