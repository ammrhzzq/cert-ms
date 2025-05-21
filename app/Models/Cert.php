<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cert extends Model
{
    use HasFactory;

    protected $fillable = [
        'cert_type',
        'iso_num',
        'comp_name',
        'comp_address1',
        'comp_address2',
        'comp_address3',
        'comp_phone1',
        'phone1_name',
        'comp_phone2',
        'phone2_name',
        'reg_date',
        'issue_date',
        'exp_date',
        'status',
        'last_edited_at',
        'last_edited_by'
    ];

    protected $casts = [
        'reg_date' => 'datetime',
        'issue_date' => 'datetime',
        'exp_date' => 'datetime',
        'last_edited_at' => 'datetime'
    ];

    // Get all verifications for the certificate
    public function verifications()
    {
        return $this->hasMany(CertVerification::class, 'cert_id');
    }

    // Get latest verification for the certificate
    public function latestVerification()
    {
        return $this->hasOne(CertVerification::class, 'cert_id')->latest();
    }

    // Get all comments for the certificate
    public function comments()
    {
        return $this->hasMany(CertComment::class, 'cert_id');
    }

    // Check if the certificate has a valid verification link
    public function hasValidVerificationLink()
    {
        return $this->latestVerification && !$this->latestVerification->isExpired();
    }

    // Get status label
    public function getStatusLabelAttribute()
    {
        $statusMap = [
            'pending_review' => 'Pending Review',
            'pending_verification' => 'Pending Client Verification',
            'client_verified' => 'Client Verified',
            'needs_revision' => 'Needs Revision',
            'pending_hod_approval' => 'Pending HOD Approval',
        ];

        return $statusMap[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}
