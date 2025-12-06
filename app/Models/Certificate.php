<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Certificate extends Model
{
    protected $fillable = [
        'student_name',
        'subject',
        'manager_name',
        'teacher_name',
        'logo_path',
        'certificate_number',
        'issue_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    /**
     * Get the formatted issue date
     */
    protected function formattedIssueDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->issue_date->format('F d, Y'),
        );
    }

    /**
     * Get the logo URL
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo_path ? asset('storage/' . $this->logo_path) : null,
        );
    }
}
