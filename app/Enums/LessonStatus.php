<?php

namespace App\Enums;

enum LessonStatus: string
{
    case Planned = 'planned';
    case Completed = 'completed';
    case Missed = 'missed';
    case Cancelled = 'cancelled';
}

