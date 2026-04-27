<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpRtiNotice extends Model
{
    protected $fillable = [
        'notice_no',
        'subject',
        'is_new',
        'start_date',
        'end_date',
        'file_path',
        'is_active',
    ];
}
