<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = ['user_id', 'slug', 'mobile', 'profile_img'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
