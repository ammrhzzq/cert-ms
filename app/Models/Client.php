<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    
    protected $fillable = [
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
    ];
}
