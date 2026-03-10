<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name_store',
        'slug',
        'description',
        'address_store',
        'latitude',
        'longitude',
        'logo',
        'is_active',
        'deactivated_by', 
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Auto generate slug dari nama toko
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shop) {
            if (empty($shop->slug)) {
                $shop->slug = Str::slug($shop->name_store);
                
                // Pastikan slug unique
                $count = static::where('slug', 'like', $shop->slug . '%')->count();
                if ($count > 0) {
                    $shop->slug = $shop->slug . '-' . ($count + 1);
                }
            }
        });
    }

     public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    // Relasi ke User (pemilik toko)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke SellerRequest
    public function sellerRequest()
    {
        return $this->hasOne(SellerRequest::class);
    }

    // Scope untuk toko aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk toko inactive/draft
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
  
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function activeVouchers()
    {
        return $this->hasMany(Voucher::class)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('valid_from')
                ->orWhere('valid_from', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('valid_until')
                ->orWhere('valid_until', '>=', now());
            });
    }
  // Di App\Models\Shop.php

// Tambahkan method ini
public function couriers()
{
    return $this->hasMany(Courier::class);
}
}