<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DPopProof extends Model
{
    protected $table = 'dpop_proofs';
    protected $fillable = [
        'jti',
        'jkt',
        'expires_at',
    ];
}
