<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Observers\OrderObserver;

class Order extends Model
{
    use HasFactory;

    // Order Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_ONGOING = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENALTY = 'penalty';
    const STATUS_RETURNED = 'returned';

    // Delivery method constants
    const DELIVERY_METHOD_PICKUP = 'pickup';
    const DELIVERY_METHOD_DELIVERY = 'delivery';


    protected $fillable = [
        'user_id',
        'product_rental_id',
        'order_code',
        'start_time',
        'end_time',
        'returned_at',
        'total_amount',
        'status',
        'payment_status',
        'paid_at',
        'delivery_method',
        'user_address_id',
        'qr_code',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'returned_at' => 'datetime',
        'paid_at' => 'datetime',
    ];



    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productRental()
    {
        return $this->belongsTo(ProductRental::class);
    }

    public function voucherUsage()
    {
        return $this->hasOne(VoucherUsage::class);
    }

    public function voucher()
    {
        return $this->hasOneThrough(
            Voucher::class,
            VoucherUsage::class,
            'order_id',
            'id',
            'id',
            'voucher_id'
        );
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function orderReturn()
    {
        return $this->hasOne(OrderReturn::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function deliveryShipment()
    {
        return $this->hasOne(Shipment::class)
            ->where('type', 'delivery')
            ->latestOfMany();
    }

    public function returnShipment()
    {
        return $this->hasOne(Shipment::class)
            ->where('type', 'return')
            ->latestOfMany();
    }

    /**
     * Get all available order statuses
     */
    public static function getAllStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_CONFIRMED,
            self::STATUS_ONGOING,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_PENALTY,
            self::STATUS_RETURNED,
        ];
    }

    /**
     * Get status label in Indonesian (STATIC VERSION)
     * Digunakan untuk: Order::getStatusLabel($status)
     */
    public static function getStatusLabel($status = null)
    {
        // Jika dipanggil tanpa parameter, return null
        if ($status === null) {
            return null;
        }

        $labels = [
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_PAID => 'Dibayar',
            self::STATUS_CONFIRMED => 'Dikonfirmasi',
            self::STATUS_ONGOING => 'Sedang Berlangsung',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_PENALTY => 'Denda',
            self::STATUS_RETURNED => 'Dikembalikan',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Get status label for current order instance (NON-STATIC VERSION)
     * Digunakan untuk: $order->status_label
     */
    public function getStatusLabelAttribute()
    {
        return self::getStatusLabel($this->status);
    }

    /**
     * Alias method untuk backward compatibility
     * Deprecated: Use getStatusLabel() instead
     */
    public static function getLabel($status)
    {
        return self::getStatusLabel($status);
    }

    /**
     * Get status badge color
     */
    public static function getStatusColor($status)
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PAID => 'info',
            self::STATUS_CONFIRMED => 'primary',
            self::STATUS_ONGOING => 'success',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_PENALTY => 'danger',
            self::STATUS_RETURNED => 'info',
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * Check if order is late
     */
    public function isLate()
    {
        return $this->status === self::STATUS_ONGOING
            && $this->end_time
            && now()->gt($this->end_time);
    }

    /**
     * Check if rental period has ended
     */
    public function isRentalExpired()
    {
        return $this->end_time && $this->end_time <= now();
    }

    /**
     * Check if order should transition to awaiting_return status
     */
    public function shouldBeAwaitingReturn()
    {
        return $this->status === self::STATUS_ONGOING
            && $this->isRentalExpired();
    }

    /**
     * Check if order is delivery type
     */
    public function isDelivery()
    {
        return $this->delivery_method === self::DELIVERY_METHOD_DELIVERY;
    }

    /**
     * Check if order is pickup type
     */
    public function isPickup()
    {
        return $this->delivery_method === self::DELIVERY_METHOD_PICKUP;
    }

    /**
     * Scopes
     */
    public function scopeDeliveryOnly($query)
    {
        return $query->where('delivery_method', self::DELIVERY_METHOD_DELIVERY);
    }

    public function scopePickupOnly($query)
    {
        return $query->where('delivery_method', self::DELIVERY_METHOD_PICKUP);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', self::STATUS_ONGOING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
    public function handoverProof()
    {
        return $this->hasOne(OrderHandoverProof::class);
    }
}
