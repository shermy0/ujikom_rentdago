<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'status',
        'created_by',
    ];

    // Relasi ke Shop
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // Relasi ke User (akun courier)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke User yang membuat (seller)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope untuk courier aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}