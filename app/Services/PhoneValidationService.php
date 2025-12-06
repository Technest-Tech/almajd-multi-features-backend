<?php

namespace App\Services;

class PhoneValidationService
{
    /**
     * Validate phone number based on country code
     */
    public function validate(string $phoneNumber, string $countryCode): bool
    {
        // Remove any non-digit characters except +
        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);

        // Basic validation rules by country (simplified)
        $rules = [
            'US' => '/^\+?1\d{10}$/', // US/Canada
            'GB' => '/^\+?44\d{10}$/', // UK
            'EG' => '/^\+?20\d{10}$/', // Egypt
            'SA' => '/^\+?966\d{9}$/', // Saudi Arabia
            'AE' => '/^\+?971\d{9}$/', // UAE
        ];

        if (isset($rules[$countryCode])) {
            return preg_match($rules[$countryCode], $phoneNumber) === 1;
        }

        // Default: at least 10 digits
        return strlen(preg_replace('/\D/', '', $phoneNumber)) >= 10;
    }

    /**
     * Format phone number with country code
     */
    public function format(string $phoneNumber, string $countryCode): string
    {
        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);

        // If doesn't start with +, add country code
        if (!str_starts_with($phoneNumber, '+')) {
            $countryCodes = [
                'US' => '+1',
                'GB' => '+44',
                'EG' => '+20',
                'SA' => '+966',
                'AE' => '+971',
            ];

            if (isset($countryCodes[$countryCode])) {
                $phoneNumber = $countryCodes[$countryCode] . $phoneNumber;
            }
        }

        return $phoneNumber;
    }
}

