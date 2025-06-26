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
        'comp_email1',
        'comp_phone2',
        'phone2_name',
        'comp_email2',
        'reg_date',
        'issue_date',
        'exp_date',
        'soa', 
        'cert_number',      
        'scope',    
        'sites',
        'status',
        'last_edited_at',
        'last_edited_by',
        'created_by'
    ];

    protected $casts = [
        'reg_date' => 'datetime',
        'issue_date' => 'datetime',
        'exp_date' => 'datetime',
        'last_edited_at' => 'datetime',
        'sites' => 'array'  // Cast sites as array for JSON handling
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

    // Get the user who created this certificate
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Get the user who last edited this certificate
    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
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

    // Get creator name attribute (optional - for easier access in views)
    public function getCreatorNameAttribute()
    {
        return $this->creator ? $this->creator->name : 'Unknown';
    }

    // Get last editor name attribute (optional - for easier access in views)
    public function getLastEditorNameAttribute()
    {
        return $this->lastEditor ? $this->lastEditor->name : 'Unknown';
    }

    // Helper method to get sites as formatted string
    public function getSitesListAttribute()
    {
        if (!$this->sites || empty($this->sites)) {
            return 'No sites specified';
        }
        
        return implode(', ', array_filter($this->sites));
    }
}