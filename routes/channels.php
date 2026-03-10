<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Order;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tracking.{orderId}', function ($user, $orderId) {
    $order = Order::findOrNew($orderId);
    
    // Allow Seller
    if ($order->productRental && $order->productRental->product && $order->productRental->product->shop && $order->productRental->product->shop->user_id === $user->id) {
        return true;
    }

    // Allow Customer (Buyer)
    if ($order->user_id === $user->id) {
        return true;
    }

    return false;
});
