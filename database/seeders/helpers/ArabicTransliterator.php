<?php

namespace Database\Seeders\Helpers;

class ArabicTransliterator
{
    /**
     * Arabic to Latin character mapping
     */
    private static array $transliterationMap = [
        // Arabic letters
        'ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'aa', 'ء' => '',
        'ب' => 'b',
        'ت' => 't', 'ة' => 'h',
        'ث' => 'th',
        'ج' => 'j',
        'ح' => 'h',
        'خ' => 'kh',
        'د' => 'd',
        'ذ' => 'th',
        'ر' => 'r',
        'ز' => 'z',
        'س' => 's',
        'ش' => 'sh',
        'ص' => 's',
        'ض' => 'd',
        'ط' => 't',
        'ظ' => 'z',
        'ع' => 'a',
        'غ' => 'gh',
        'ف' => 'f',
        'ق' => 'q',
        'ك' => 'k',
        'ل' => 'l',
        'م' => 'm',
        'ن' => 'n',
        'ه' => 'h',
        'و' => 'w',
        'ي' => 'y', 'ى' => 'a',
        
        // Vowel marks (tashkeel)
        'َ' => 'a', 'ً' => 'an',
        'ُ' => 'u', 'ٌ' => 'un',
        'ِ' => 'i', 'ٍ' => 'in',
        'ّ' => '', // shadda
        'ْ' => '', // sukun
        
        // Special characters
        'ـ' => '', // tatweel
        'ى' => 'a',
        'ئ' => 'e',
        'ؤ' => 'o',
        
        // Numbers (Arabic-Indic)
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        
        // Common Arabic words transliteration
        'عبد' => 'abd',
        'عبدال' => 'abdel',
    ];

    /**
     * Transliterate Arabic text to Latin characters
     */
    public static function transliterate(string $text): string
    {
        // Convert to lowercase first
        $text = mb_strtolower($text, 'UTF-8');
        
        // Replace Arabic characters
        $transliterated = strtr($text, self::$transliterationMap);
        
        // Remove any remaining non-ASCII characters
        $transliterated = preg_replace('/[^\x20-\x7E]/', '', $transliterated);
        
        // Replace spaces and special characters with underscores
        $transliterated = preg_replace('/[^a-z0-9]+/', '_', $transliterated);
        
        // Remove leading/trailing underscores
        $transliterated = trim($transliterated, '_');
        
        // Replace multiple underscores with single
        $transliterated = preg_replace('/_+/', '_', $transliterated);
        
        // Limit length
        $transliterated = substr($transliterated, 0, 50);
        
        // Remove trailing underscore if any
        $transliterated = rtrim($transliterated, '_');
        
        return $transliterated ?: 'student';
    }

    /**
     * Generate email from ID and name
     */
    public static function generateEmail(int $id, string $name): string
    {
        $transliterated = self::transliterate($name);
        
        // Ensure we have something to work with
        if (empty($transliterated)) {
            $transliterated = 'student';
        }
        
        return "student_{$id}_{$transliterated}@almajd.com";
    }

    /**
     * Ensure email uniqueness by appending suffix if needed
     */
    public static function ensureUniqueEmail(string $email, array $existingEmails): string
    {
        $originalEmail = $email;
        $counter = 2;
        
        while (in_array($email, $existingEmails)) {
            // Split email into local and domain parts
            [$local, $domain] = explode('@', $originalEmail);
            $email = "{$local}_{$counter}@{$domain}";
            $counter++;
        }
        
        return $email;
    }

    /**
     * Sanitize name for safe storage
     */
    public static function sanitizeName(string $name): string
    {
        // Trim whitespace
        $name = trim($name);
        
        // Replace multiple spaces with single space
        $name = preg_replace('/\s+/', ' ', $name);
        
        return $name;
    }
}

