<?php

namespace App\Models;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'receiver_name',
        'receiver_phone',
        'address',
        'latitude',
        'longitude',
        'notes',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relasi: alamat milik user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: alamat bisa dipakai banyak order
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
