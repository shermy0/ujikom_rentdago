<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'role',
        'avatar',
        'password',
        'phone_verified_at',
        'user_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'user_verified_at'  => 'datetime',
    ];

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'shop_id');
    }

    public function vouchers()
    {
         return $this->belongsToMany(Voucher::class, 'user_vouchers')
                    ->withPivot('claimed_at')  // ← Hapus 'usage_count'
                    ->withTimestamps()
                    ->using(UserVoucher::class);
    }


    
public function courier()
{
    return $this->hasOne(Courier::class);
}

public function couriersCreated()
{
    return $this->hasMany(Courier::class, 'created_by');
}

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }


}
