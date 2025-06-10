<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'file_path',
        'description',
        'is_active',
        'cert_type',
        'uploaded_by',
        'version'
    ];
    
    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
    // Get active template
    public static function getActiveTemplate($cert_type = null)
    {
        $query = self::where('is_active', true);
        
        if ($cert_type) {
            $query->where('cert_type', $cert_type);
        }
        
        return $query->first();
    }
}