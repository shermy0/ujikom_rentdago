<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SellerChatController extends Controller
{
    public function index()
    {
        // Sekarang logika sudah di Livewire
        return view('seller.chat.index')->with('title', 'Chat');;
    }

    public function show($customerId)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()->back()->with('error', 'Anda belum memiliki toko');
        }

        $conversation = Conversation::where('shop_id', $shop->id)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        return view('seller.chat.show', compact('customerId'));
    }

    /**
     * Get unread chat count for seller
     */
    public function getUnreadCount()
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return response()->json(['count' => 0]);
        }

        // Get all conversations for this shop
        $conversations = Conversation::where('shop_id', $shop->id)->get();

        // Sum up all unread messages from customers
        $unreadCount = $conversations->sum(function($conversation) {
            return $conversation->unreadForSeller();
        });

        return response()->json(['count' => $unreadCount]);
    }
}