<?php
// App/Models/Conversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'shop_id',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }

    /**
     * Get unread message count for specific user
     */
    public function unreadCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get unread messages for seller (not sent by seller/shop owner)
     */
    public function unreadForSeller()
    {
        $shopOwnerId = $this->shop->user_id;
        
        return $this->messages()
            ->where('sender_id', '!=', $shopOwnerId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get unread messages for customer
     */
    public function unreadForCustomer()
    {
        return $this->messages()
            ->where('sender_id', '!=', $this->customer_id)
            ->where('is_read', false)
            ->count();
    }
}