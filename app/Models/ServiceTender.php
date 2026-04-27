<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTender extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'tender_date',
        'file_name',
        'file_path',
        'is_active',
    ];
}
