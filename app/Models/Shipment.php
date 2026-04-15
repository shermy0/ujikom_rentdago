<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Storage facade dihapus — tidak digunakan lagi

class Shipment extends Model
{
    use HasFactory;

    // Shipment Type Constants
    const TYPE_DELIVERY = 'delivery';
    const TYPE_RETURN = 'return';

    // Shipment Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_DRIVER_ASSIGNED = 'assigned'; // Alias
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_ON_THE_WAY = 'on_the_way';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_RETURNED = 'returned';
    const STATUS_REJECTED = 'rejected';

    // Distance Thresholds (in meters)
    const ARRIVAL_THRESHOLD = 20; // 20 meters
    const MAX_VALIDATION_THRESHOLD = 50; // Maximum allowed for validation

    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     * 
     * @param float $lat1 Latitude 1
     * @param float $lon1 Longitude 1
     * @param float $lat2 Latitude 2
     * @param float $lon2 Longitude 2
     * @return float Distance in meters
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2); // Return distance in meters with 2 decimal places
    }

    /**
     * Calculate distance from current location to destination
     * 
     * @param float $destLat Destination latitude
     * @param float $destLng Destination longitude
     * @return float|null Distance in meters, or null if no location data
     */
    public function calculateDistanceTo($destLat, $destLng)
    {
        if (!$this->last_lat || !$this->last_lng) {
            return null;
        }

        return self::calculateDistance(
            (float) $this->last_lat,
            (float) $this->last_lng,
            (float) $destLat,
            (float) $destLng
        );
    }

    /**
     * Check if current location is within arrival threshold
     * 
     * @param float $shopLat Shop latitude
     * @param float $shopLng Shop longitude
     * @return bool
     */
    public function isWithinArrivalThreshold($shopLat, $shopLng)
    {
        $distance = $this->calculateDistanceTo($shopLat, $shopLng);

        if ($distance === null) {
            return false;
        }

        return $distance <= self::ARRIVAL_THRESHOLD;
    }

    /**
     * Get all available shipment types
     */
    public static function getAllTypes()
    {
        return [
            self::TYPE_DELIVERY,
            self::TYPE_RETURN,
        ];
    }

    /**
     * Get all available shipment statuses
     */
    public static function getAllStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ASSIGNED,
            self::STATUS_PICKED_UP,
            self::STATUS_ON_THE_WAY,
            self::STATUS_ARRIVED,
            self::STATUS_DELIVERED,
            self::STATUS_FAILED,
            self::STATUS_RETURNED,
            self::STATUS_REJECTED
        ];
    }

    /**
     * Get type label in Indonesian
     */
    public static function getTypeLabel($type)
    {
        $labels = [
            self::TYPE_DELIVERY => 'Pengiriman',
            self::TYPE_RETURN => 'Pengembalian',
        ];

        return $labels[$type] ?? $type;
    }

    /**
     * Get status label in Indonesian
     */
    public static function getStatusLabel($status)
    {
        $labels = [
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_ASSIGNED => 'Ditugaskan',
            self::STATUS_PICKED_UP => 'Sudah Diambil',
            self::STATUS_ON_THE_WAY => 'Dalam Perjalanan',
            self::STATUS_ARRIVED => 'Sudah Sampai',
            self::STATUS_DELIVERED => 'Diterima Customer',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_RETURNED => 'Dikembalikan',
            self::STATUS_REJECTED => 'Ditolak',
        ];

        return $labels[$status] ?? $status;
    }

    protected $fillable = [
        'order_id',
        'courier_id',
        'type',
        'status',
        'pickup_address_snapshot',
        'delivery_address_snapshot',
        'is_tracking_active',
        'last_lat',
        'last_lng',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'failed_at',
        'courier_notes',
        'rejected_at',
        'rejection_reason',
        'rejected_by',
        'delivery_proof_photo',
        'delivery_proof_photo_at'
    ];

    protected $casts = [
        'is_tracking_active' => 'boolean',
        'last_lat' => 'decimal:8',
        'last_lng' => 'decimal:8',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'rejected_by' => 'array',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * Assign courier to shipment
     */
    public function assignCourier($courierId, $notes = null)
    {
        if ($this->status === self::STATUS_PENDING) {
            return $this->update([
                'courier_id' => $courierId,
                'status' => self::STATUS_ASSIGNED,
                'assigned_at' => now(),
                'courier_notes' => $notes,
            ]);
        }
        return false;
    }

    /**
     * Mark shipment as picked up
     */
    public function markAsPickedUp()
    {
        if ($this->status === self::STATUS_ASSIGNED) {
            return $this->update([
                'status' => self::STATUS_PICKED_UP,
                'picked_up_at' => now(),
            ]);
        }
        return false;
    }

    /**
     * Mark shipment as on the way
     */
    public function markAsOnTheWay()
    {
        if ($this->status === self::STATUS_PICKED_UP) {
            return $this->update([
                'status' => self::STATUS_ON_THE_WAY,
                'is_tracking_active' => true,
            ]);
        }
        return false;
    }

    /**
     * Mark shipment as arrived
     */
    public function markAsArrived()
    {
        if ($this->status === self::STATUS_ON_THE_WAY) {
            return $this->update([
                'status' => self::STATUS_ARRIVED,
                // Do not set delivered_at yet, only when handed over
                // Keep tracking active as per requirements
                'is_tracking_active' => true,
            ]);
        }
        return false;
    }

    /**
     * Mark shipment as handed over
     */
    public function markAsHandedOver()
    {
        if ($this->status === self::STATUS_ARRIVED) {
            return $this->update([
                'status' => self::STATUS_DELIVERED,
                'delivered_at' => now(),
                'is_tracking_active' => false,
            ]);
        }
        return false;
    }

    /**
     * Mark shipment as failed
     */
    public function markAsFailed($reason = null)
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'courier_notes' => $reason,
            'is_tracking_active' => false,
        ]);
    }

    /**
     * Update tracking location
     */
    public function updateLocation($latitude, $longitude)
    {
        if ($this->is_tracking_active) {
            return $this->update([
                'last_lat' => $latitude,
                'last_lng' => $longitude,
            ]);
        }
        return false;
    }

    /**
     * Check if shipment is delivery type
     */
    public function isDelivery()
    {
        return $this->type === self::TYPE_DELIVERY;
    }

    /**
     * Check if shipment is return type
     */
    public function isReturn()
    {
        return $this->type === self::TYPE_RETURN;
    }

    /**
     * Check if shipment is in progress
     */
    public function isInProgress()
    {
        return in_array($this->status, [
            self::STATUS_ASSIGNED,
            self::STATUS_PICKED_UP,
            self::STATUS_ON_THE_WAY,
        ]);
    }

    /**
     * Check if shipment is completed
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_ARRIVED;
    }

    /**
     * Reject shipment by courier
     */
    public function rejectByCourier($courierId, $reason = null)
    {
        // Allow rejection from pending status (courier hasn't accepted yet)
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_ASSIGNED])) {
            return false;
        }

        // Add courier to rejected list
        $this->addToRejectedList($courierId);

        // Set status to rejected so seller knows to reassign
        // courier_id is kept so seller knows who rejected
        return $this->update([
            'status' => self::STATUS_REJECTED,  // Set to rejected so seller sees it
            'courier_id' => null,               // Clear current courier
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Accept shipment by courier - changes status from pending to assigned
     */
    public function acceptByCourier($courierId)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        return $this->update([
            'courier_id' => $courierId,
            'status' => self::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Check if courier has already rejected this shipment
     */
    public function hasBeenRejectedBy($courierId)
    {
        $rejectedBy = $this->rejected_by ?? [];
        return in_array($courierId, $rejectedBy);
    }

    /**
     * Add courier to rejected list
     */
    public function addToRejectedList($courierId)
    {
        $rejectedBy = $this->rejected_by ?? [];

        if (!in_array($courierId, $rejectedBy)) {
            $rejectedBy[] = $courierId;
            $this->rejected_by = $rejectedBy;
            $this->save();
        }
    }

    /**
     * Get list of courier IDs who rejected this shipment
     */
    public function getRejectedCourierIds()
    {
        return $this->rejected_by ?? [];
    }

    public function getDeliveryProofUrlAttribute()
    {
        if (!$this->delivery_proof_photo) {
            return null;
        }

        return asset($this->delivery_proof_photo);
    }

    /**
     * Check if delivery proof photo exists
     */
    public function hasDeliveryProof()
    {
        return !empty($this->delivery_proof_photo);
    }
}
