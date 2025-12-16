<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarTeacher extends Model
{
    use HasFactory;

    protected $table = 'calendar_teachers';

    protected $fillable = [
        'name',
        'whatsapp', // Database column is 'whatsapp', not 'whatsapp_number'
    ];

    /**
     * Get all timetable entries for this teacher
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(CalendarTeacherTimetable::class, 'teacher_id');
    }

    /**
     * Get all exceptional classes for this teacher
     */
    public function exceptionalClasses(): HasMany
    {
        return $this->hasMany(CalendarExceptionalClass::class, 'teacher_id');
    }
}
