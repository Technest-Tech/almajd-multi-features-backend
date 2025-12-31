# Old Database Migration - Quick Start

## 🚀 Quick Setup (5 minutes)

### 1. Files Already in Place
All migration files have been created:
- ✅ Main seeder: `database/seeders/OldDataMigrationSeeder.php`
- ✅ Test seeder: `database/seeders/TestOldDataMigrationSeeder.php`
- ✅ Helper classes in: `database/seeders/helpers/`
- ✅ Documentation: `OLD_DATABASE_MIGRATION.md`

### 2. Your SQL Files
The SQL files are already copied to: `database/data/old_database/`
- ✅ currencies.sql
- ✅ users.sql
- ✅ families.sql
- ✅ family_tutor.sql

### 3. Run Migration

**⚠️ BACKUP YOUR DATABASE FIRST!**

```bash
# Backup current database
php artisan db:dump

# Run the migration
php artisan db:seed --class=OldDataMigrationSeeder

# Run tests to verify
php artisan db:seed --class=TestOldDataMigrationSeeder
```

## 📊 What Happens

### Teachers (from old `users` table)
- Original ID 48 → New ID 10048 (+10000 offset)
- Email & password preserved
- Bank details preserved
- Type: admin (if user_type_id=0) or teacher (if user_type_id=1)

### Students (from old `families` table)
- Original ID preserved (family ID 38 stays as 38)
- **Email generated**: `student_38_salam_wsara@almajd.com`
- **Password**: `Student@123` (same for all, they can change it)
- Currency mapped: 1=USD, 2=EUR, 3=CAD, 4=GBP
- Country auto-detected from phone number

### Relationships (from `family_tutor` table)
- Teacher-student links preserved
- Teacher IDs adjusted (+10000)
- Example: Teacher 48 → 10048 teaches Student 37

## 📋 Expected Results

After migration you should see:
```
╔════════════════╦═══════╗
║ Metric         ║ Count ║
╠════════════════╬═══════╣
║ Admins         ║ ~1    ║
║ Teachers       ║ ~155  ║
║ Students       ║ ~1451 ║
║ Relationships  ║ ~2678 ║
║ Errors         ║ 0     ║
║ Warnings       ║ 0-5   ║
╚════════════════╩═══════╝
```

## 🔍 Check the Results

### View Migration Log
```bash
cat storage/logs/migration_*.log
```

### Run Validation Tests
```bash
php artisan db:seed --class=TestOldDataMigrationSeeder
```

### Manual Checks
```bash
# Count users by type
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\User::where('user_type', 'teacher')->count()
>>> \App\Models\User::where('user_type', 'student')->count()

# Check a sample student
>>> $student = \App\Models\User::where('user_type', 'student')->first()
>>> $student->email
>>> $student->whatsapp_number

# Check relationships
>>> DB::table('teacher_student')->count()
```

## 🔐 Login Credentials

### Teachers
- Email: Their original email (unchanged)
- Password: Their original password (unchanged)
- Example: `ebrahim@admin.com` / [original password]

### Students (NEW)
- Email: Generated format `student_[id]_[name]@almajd.com`
- Password: `Student@123` (all students)
- Example: `student_38_salam_wsara@almajd.com` / `Student@123`

**⚠️ You MUST notify students of their new credentials!**

## ❌ Rollback (if needed)

If something goes wrong:

```bash
# Option 1: Restore from backup
php artisan db:restore

# Option 2: Manual cleanup
php artisan tinker
>>> DB::table('teacher_student')->truncate();
>>> DB::table('users')->where('id', '>=', 10000)->delete(); // Teachers
>>> DB::table('users')->where('user_type', 'student')->delete(); // Students
```

## 📞 Common Issues

### Issue: "Email already exists"
**Fix**: Some teachers might already be in the system. Check the log to see which ones were skipped.

### Issue: Country is NULL for students
**Fix**: Phone number format not recognized. You can update manually:
```sql
UPDATE users SET country = 'SA' WHERE whatsapp_number LIKE '+966%';
UPDATE users SET country = 'US' WHERE whatsapp_number LIKE '+1%' AND country IS NULL;
```

### Issue: Test students can't login
**Check**:
1. Is the email correct? Should be `student_[id]_[name]@almajd.com`
2. Password should be: `Student@123`
3. Run the test seeder to verify passwords

## 📚 Full Documentation

For detailed information, see: `OLD_DATABASE_MIGRATION.md`

## ✨ Success Checklist

- [ ] Database backed up
- [ ] Migration completed without errors
- [ ] Test seeder shows ✓ for all tests
- [ ] Sample teacher can login
- [ ] Sample student can login with default password
- [ ] Teacher-student relationships work in the app
- [ ] Migration log saved in `storage/logs/`

---

**Need help?** Check the log file or run the test seeder for detailed diagnostics.







