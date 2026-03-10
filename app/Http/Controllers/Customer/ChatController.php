<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function index()
    {
        // Sekarang logika sudah di Livewire
        return view('customer.chat.index')->with('title', 'Chat');
    }

    public function show($shopId)
    {
        return view('customer.chat.show', compact('shopId'));
    }
}