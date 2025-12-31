#!/usr/bin/env php
<?php

/**
 * Pre-Migration Analysis Script
 * 
 * Run this before executing the migration to see what will be imported
 * Usage: php analyze-old-data.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$dataPath = __DIR__ . '/../data/old_database';

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         OLD DATABASE PRE-MIGRATION ANALYSIS                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Check if files exist
$requiredFiles = ['currencies.sql', 'users.sql', 'families.sql', 'family_tutor.sql'];
$allFilesExist = true;

echo "📁 Checking for required files...\n";
echo "─────────────────────────────────\n";
foreach ($requiredFiles as $file) {
    $path = $dataPath . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        $sizeFormatted = number_format($size / 1024, 2);
        echo "✓ {$file} ({$sizeFormatted} KB)\n";
    } else {
        echo "✗ {$file} - NOT FOUND!\n";
        $allFilesExist = false;
    }
}

if (!$allFilesExist) {
    echo "\n⚠️  ERROR: Some required files are missing!\n";
    echo "Please ensure all SQL files are in: {$dataPath}/\n\n";
    exit(1);
}

echo "\n";

// Analyze currencies
echo "💱 CURRENCIES\n";
echo "─────────────\n";
$content = file_get_contents($dataPath . '/currencies.sql');
preg_match_all('/\((\d+),\s*\'([^\']+)\',\s*\'([^\']+)\'\)/', $content, $matches);
for ($i = 0; $i < count($matches[0]); $i++) {
    $id = $matches[1][$i];
    $name = $matches[2][$i];
    $symbol = $matches[3][$i];
    echo "  {$id}: {$symbol} ({$name})\n";
}

echo "\n";

// Analyze users (teachers/admins)
echo "👨‍🏫 USERS (Teachers & Admins)\n";
echo "──────────────────────────────\n";
$content = file_get_contents($dataPath . '/users.sql');
preg_match_all('/\((\d+),/', $content, $idMatches);
$totalUsers = count($idMatches[0]);

preg_match_all('/\'[^\']+\',\s*(\d+),/', $content, $typeMatches);
$userTypes = array_count_values($typeMatches[1]);
$admins = $userTypes[0] ?? 0;
$teachers = $userTypes[1] ?? 0;

$minId = min(array_map('intval', $idMatches[1]));
$maxId = max(array_map('intval', $idMatches[1]));

echo "  Total: {$totalUsers}\n";
echo "  Admins: {$admins}\n";
echo "  Teachers: {$teachers}\n";
echo "  ID Range: {$minId} - {$maxId}\n";
echo "\n";
echo "  → After migration, teachers will have IDs: " . ($minId + 10000) . " - " . ($maxId + 10000) . "\n";

echo "\n";

// Analyze families (students)
echo "👨‍👩‍👧‍👦 FAMILIES (Students)\n";
echo "───────────────────────\n";
$content = file_get_contents($dataPath . '/families.sql');
preg_match_all('/\((\d+),/', $content, $idMatches);
$totalFamilies = count($idMatches[0]);

$minId = min(array_map('intval', $idMatches[1]));
$maxId = max(array_map('intval', $idMatches[1]));

// Count by currency
preg_match_all('/,\s*(\d+),\s*\'[\d-]+ [\d:]+\'/', $content, $currencyMatches);
$currencies = array_count_values($currencyMatches[1]);
arsort($currencies);

// Count by country code
preg_match_all('/\'\+(\d+)/', $content, $countryMatches);
$countries = array_count_values($countryMatches[1]);
arsort($countries);

echo "  Total: {$totalFamilies}\n";
echo "  ID Range: {$minId} - {$maxId}\n";
echo "\n";
echo "  Currency Distribution:\n";
foreach (array_slice($currencies, 0, 5) as $currencyId => $count) {
    $currencyName = ['1' => 'USD', '2' => 'EUR', '3' => 'CAD', '4' => 'GBP'][$currencyId] ?? 'Unknown';
    $percentage = round($count / $totalFamilies * 100, 1);
    echo "    {$currencyName}: {$count} ({$percentage}%)\n";
}
echo "\n";
echo "  Top Country Codes:\n";
foreach (array_slice($countries, 0, 5) as $code => $count) {
    $countryName = [
        '1' => 'US/Canada',
        '44' => 'UK',
        '49' => 'Germany',
        '966' => 'Saudi Arabia',
        '218' => 'Libya',
    ][$code] ?? "Unknown (+{$code})";
    $percentage = round($count / $totalFamilies * 100, 1);
    echo "    +{$code} ({$countryName}): {$count} ({$percentage}%)\n";
}

echo "\n";

// Analyze family_tutor (relationships)
echo "🔗 TEACHER-STUDENT RELATIONSHIPS\n";
echo "─────────────────────────────────\n";
$content = file_get_contents($dataPath . '/family_tutor.sql');
preg_match_all('/\((\d+),\s*(\d+),\s*(\d+),/', $content, $matches);
$totalRelationships = count($matches[0]);

$teacherIds = array_unique($matches[2]);
$studentIds = array_unique($matches[3]);

$uniqueTeachers = count($teacherIds);
$uniqueStudents = count($studentIds);

$avgStudentsPerTeacher = round($totalRelationships / max($uniqueTeachers, 1), 1);
$avgTeachersPerStudent = round($totalRelationships / max($uniqueStudents, 1), 1);

echo "  Total Relationships: {$totalRelationships}\n";
echo "  Unique Teachers: {$uniqueTeachers}\n";
echo "  Unique Students: {$uniqueStudents}\n";
echo "  Avg Students per Teacher: {$avgStudentsPerTeacher}\n";
echo "  Avg Teachers per Student: {$avgTeachersPerStudent}\n";

echo "\n";

// Sample data
echo "📋 SAMPLE DATA PREVIEW\n";
echo "──────────────────────\n";

// Sample teacher
preg_match('/\((\d+),\s*\'([^\']+)\',\s*\'([^\']+)\'/', file_get_contents($dataPath . '/users.sql'), $teacherMatch);
if ($teacherMatch) {
    echo "  Sample Teacher:\n";
    echo "    Name: {$teacherMatch[2]}\n";
    echo "    Email: {$teacherMatch[3]}\n";
    echo "    Old ID: {$teacherMatch[1]} → New ID: " . ($teacherMatch[1] + 10000) . "\n";
}

echo "\n";

// Sample family
preg_match('/\((\d+),\s*\'([^\']+)\',\s*\'([^\']+)\'/', file_get_contents($dataPath . '/families.sql'), $familyMatch);
if ($familyMatch) {
    $id = $familyMatch[1];
    $name = $familyMatch[2];
    $phone = $familyMatch[3];
    
    // Simulate email generation
    $transliterated = preg_replace('/[^\x20-\x7E]/', '', $name);
    $transliterated = preg_replace('/[^a-z0-9]+/i', '_', strtolower($transliterated));
    $transliterated = trim($transliterated, '_');
    $email = "student_{$id}_{$transliterated}@almajd.com";
    
    echo "  Sample Student:\n";
    echo "    Name: {$name}\n";
    echo "    Phone: {$phone}\n";
    echo "    Generated Email: {$email}\n";
    echo "    Default Password: Student@123\n";
}

echo "\n";

// Migration summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    MIGRATION SUMMARY                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "  This migration will:\n";
echo "  • Import {$admins} admin(s) with IDs 10000+\n";
echo "  • Import {$teachers} teacher(s) with IDs 10000+\n";
echo "  • Import {$totalFamilies} student(s) keeping original IDs\n";
echo "  • Create {$totalRelationships} teacher-student relationships\n";
echo "\n";
echo "  All students will get:\n";
echo "  • Generated emails: student_[id]_[name]@almajd.com\n";
echo "  • Default password: Student@123\n";
echo "  • Currencies mapped from old system\n";
echo "  • Countries detected from phone numbers\n";
echo "\n";
echo "  ✓ Ready to migrate!\n";
echo "\n";
echo "  Next steps:\n";
echo "  1. Backup your database: php artisan db:dump\n";
echo "  2. Run migration: php artisan db:seed --class=OldDataMigrationSeeder\n";
echo "  3. Run tests: php artisan db:seed --class=TestOldDataMigrationSeeder\n";
echo "\n";







