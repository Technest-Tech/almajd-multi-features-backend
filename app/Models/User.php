<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Currency;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'whatsapp_number',
        'country',
        'currency',
        'hour_price',
        'bank_name',
        'account_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
            'currency' => Currency::class,
            'hour_price' => 'decimal:2',
        ];
    }

    /**
     * Get courses where this user is a student
     */
    public function coursesAsStudent(): HasMany
    {
        return $this->hasMany(Course::class, 'student_id');
    }

    /**
     * Get courses where this user is a teacher
     */
    public function coursesAsTeacher(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /**
     * Get lessons created by this user
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'created_by');
    }

    /**
     * Get students assigned to this teacher
     */
    public function assignedStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_student', 'teacher_id', 'student_id')
            ->withTimestamps();
    }

    /**
     * Get teachers assigned to this student
     */
    public function assignedTeachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_student', 'student_id', 'teacher_id')
            ->withTimestamps();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->user_type === UserType::Admin;
    }

    /**
     * Check if user is teacher
     */
    public function isTeacher(): bool
    {
        return $this->user_type === UserType::Teacher;
    }

    /**
     * Check if user is student
     */
    public function isStudent(): bool
    {
        return $this->user_type === UserType::Student;
    }
}
