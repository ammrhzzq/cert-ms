<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'cert_id',
        'token',
        'expires_at',
        'is_verified',
        'verified_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean'
    ];

    // Relationship with Cert model
    public function certificate()
    {
        return $this->belongsTo(Cert::class, 'cert_id');
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}