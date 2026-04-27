<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'page_url',
        'image_path',
        'is_active',
        'added_by',
        'updated_by',
        'page_title',
        'organization',
    ];

    public function banner_added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function banner_updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
