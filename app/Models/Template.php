<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'cert_type',
        'name',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
    ];
}