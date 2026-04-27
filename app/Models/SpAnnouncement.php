<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpAnnouncement extends Model
{
    protected $fillable = [
        'type',
        'ann_no',
        'subject',
        'is_new',
        'start_date',
        'end_date',
        'file_path',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
