<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedNonce extends Model
{
    protected $fillable = [
        'token_id',
        'nonce',
        'expires_at',
    ];
}
