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
}
