<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'shop_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_transaction',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'integer',
        'max_discount' => 'integer',
        'min_transaction' => 'integer',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_vouchers')
            ->withPivot('claimed_at')
            ->withTimestamps();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    // Check if voucher is valid
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    // Check if user can claim voucher
    public function canBeClaimed($userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $userVoucher = $this->users()->where('user_id', $userId)->first();
        
        return !$userVoucher; // User belum klaim
    }

    // Check if user can use voucher
    public function canBeUsed($userId, $transactionAmount): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Cek apakah user sudah claim voucher
        $userVoucher = $this->users()->where('user_id', $userId)->first();

        if (!$userVoucher) {
            return false; // Belum diklaim
        }

        // Cek apakah user sudah pernah menggunakan voucher ini
        $hasUsed = $this->usages()
            ->where('user_id', $userId)
            ->exists();

        if ($hasUsed) {
            return false; // Sudah pernah digunakan
        }

        if ($transactionAmount < $this->min_transaction) {
            return false; // Transaksi di bawah minimum
        }

        return true;
    }

    // Calculate discount
    public function calculateDiscount($amount): int
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($amount * $this->discount_value) / 100;
            
            if ($this->max_discount && $discount > $this->max_discount) {
                return $this->max_discount;
            }
            
            return (int) $discount;
        }

        // Fixed discount
        return min($this->discount_value, $amount);
    }

    // Get formatted discount
    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value . '%';
        }

        return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
    }

    // Get status label
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Nonaktif';
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return 'Belum Aktif';
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return 'Kadaluarsa';
        }

        return 'Aktif';
    }

    // Check if user has used this voucher
    public function hasBeenUsedBy($userId): bool
    {
        return $this->usages()
            ->where('user_id', $userId)
            ->exists();
    }

    public function User()
    {
        return $this->belongsToMany(User::class, 'user_vouchers')
                    ->withPivot('claimed_at')
                    ->withTimestamps()
                    ->using(UserVoucher::class);
    }
}