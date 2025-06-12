<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cert_id',
        'comment',
        'commented_by',
        'comment_type'
    ];

    // Relationship with Cert model
    public function certificate()
    {
        return $this->belongsTo(Cert::class, 'cert_id');
    }

    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class, 'commented_by');
    }
}