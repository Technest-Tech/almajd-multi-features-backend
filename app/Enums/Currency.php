<?php

namespace App\Enums;

enum Currency: string
{
    case USD = 'USD';
    case GBP = 'GBP';
    case EUR = 'EUR';
    case EGP = 'EGP';
    case SAR = 'SAR';
    case AED = 'AED';
    case CAD = 'CAD';
    
    public function symbol(): string
    {
        return match($this) {
            self::USD => '$',
            self::GBP => '£',
            self::EUR => '€',
            self::EGP => 'E£',
            self::SAR => '﷼',
            self::AED => 'د.إ',
            self::CAD => 'C$',
        };
    }
}

