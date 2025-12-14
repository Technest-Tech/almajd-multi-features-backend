<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAutoLoginCredential extends Model
{
    use HasFactory;

    protected $table = 'client_auto_login_credentials';

    protected $fillable = [
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get decrypted password
     */
    public function getDecryptedPassword(): string
    {
        try {
            return decrypt($this->password);
        } catch (\Exception $e) {
            // If decryption fails, return the password as-is (might be plain text)
            return $this->password;
        }
    }

    /**
     * Set encrypted password
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = encrypt($value);
    }
}
