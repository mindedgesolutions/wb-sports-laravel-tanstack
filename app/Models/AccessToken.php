<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $fillable = [
        'jti',
        'user_id',
        'dpop_jkt',
        'issued_at',
        'expires_at',
        'revoked'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
