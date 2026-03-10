<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderHandoverProof extends Model
{
    use HasFactory;

    protected $table = 'order_handover_proofs';

    protected $fillable = [
        'order_id',
        'photo_path',
        'type',
        'taken_at',
        'notes',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    /**
     * Relasi ke order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
