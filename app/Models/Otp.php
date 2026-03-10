<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
    'user_id',
    'phone',
    'code',
    'expired_at',
];

 protected $dates = ['expired_at'];

    protected $casts = [
    'expired_at' => 'datetime',
];
}
