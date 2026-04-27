<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    protected $fillable = ['user_id', 'login_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
