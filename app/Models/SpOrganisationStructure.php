<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpOrganisationStructure extends Model
{
    protected $fillable = [
        'designation',
        'slug',
        'show_order',
        'is_active',
    ];
}
