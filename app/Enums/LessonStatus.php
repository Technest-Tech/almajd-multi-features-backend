<?php

namespace App\Enums;

enum LessonStatus: string
{
    case Present = 'present';
    case Cancelled = 'cancelled';
}

