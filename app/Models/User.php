<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_approved',
        'email_verified_at',
        'email_verification_token',
        'email_verification_expires_at',
        'email_verification_attempts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'is_approved' => 'boolean',
    ];

    /**
     * Check if user is HOD
     */
    public function isHod()
    {
        return $this->role === 'hod';
    }

    /**
     * Check if user's email has been verified
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified
     */
    public function markEmailAsVerified()
    {
        $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'email_verification_token' => null,
            'email_verification_expires_at' => null,
            'email_verification_attempts' => 0,
        ])->save();
    }

    /**
     * Generate email verification token
     */
    public function generateEmailVerificationToken()
    {
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->forceFill([
            'email_verification_token' => $token,
            'email_verification_expires_at' => Carbon::now()->addMinutes(10),
            'email_verification_attempts' => 0,
        ])->save();
    }

    /**
     * Check if the verification token is valid
     */
    public function isValidEmailVerificationToken($token)
    {
        return $this->email_verification_token === $token &&
               $this->email_verification_expires_at &&
               $this->email_verification_expires_at->isFuture();
    }

    /**
     * Increment verification attempts
     */
    public function incrementVerificationAttempts()
    {
        $this->increment('email_verification_attempts');
    }
}