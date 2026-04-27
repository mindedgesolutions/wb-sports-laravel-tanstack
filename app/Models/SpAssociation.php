<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpAssociation extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'address',
        'website',
        'email',
        'phone_1',
        'phone_2',
        'fax',
        'is_active'
    ];
}
