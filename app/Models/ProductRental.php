<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductRental extends Model
{
    protected $fillable = [
        'product_id',
        'price',
        'cycle_value',
        'penalties_price',
        'penalties_cycle_value',
        'is_delivery',
        
    ];

    protected $casts = [
        'price' => 'integer',
        'cycle_value' => 'integer',
        'penalties_price' => 'integer',
        'penalties_cycle_value' => 'integer'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // HAPUS/COMMENT CODE INI UNTUK SEMENTARA:
    // public function pickupLocation(): HasOne
    // {
    //     return $this->hasOne(PickupLocation::class, 'rental_id');
    // }

    // /**
    //  * Check if rental has a pickup location
    //  */
    // public function hasPickup(): bool
    // {
    //     return $this->pickupLocation()->exists();
    // }

    // Accessor: Convert enum ke array untuk checkbox
    protected function isDelivery(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === 'pickup_delivery') {
                    return ['pickup', 'delivery'];
                }
                return [$value]; // 'pickup' atau 'delivery' jadi array
            },
            set: function ($value) {
                // Input dari form: array ['pickup'] atau ['delivery'] atau ['pickup', 'delivery']
                if (is_array($value)) {
                    sort($value); // Sortir dulu
                    if (count($value) === 2) {
                        return 'pickup_delivery'; // Keduanya dipilih
                    }
                    return $value[0]; // Salah satu
                }
                return $value;
            }
        );
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedPenaltiesPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->penalties_price, 0, ',', '.');
    }

    public function getDeliveryLabelAttribute(): string
    {
        return $this->is_delivery === 'delivery' ? 'Antar' : 'Ambil Sendiri';
    }

    public function hasPickup(): bool
    {
        return $this->is_delivery === 'pickup' || $this->is_delivery === 'picup_delivery';
    }

    public function hasDelivery(): bool
    {
        return $this->is_delivery === 'delivery' || $this->is_delivery === 'pickup_delivery';
    }
      public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
