<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpContact extends Model
{
    protected $fillable = [
        'designation',
        'department',
        'name',
        'address',
        'email',
        'phone_1',
        'phone_2',
        'fax',
        'is_active',
        'show_order',
    ];
}
