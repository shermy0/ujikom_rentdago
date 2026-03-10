<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class SellerChatList extends Component
{
    public $conversations = [];
    public $search = '';
    public $totalUnread = 0;

    public function mount()
    {
        $this->loadConversations();
    }

    public function loadConversations()
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            $this->conversations = collect();
            return;
        }

        $query = Conversation::where('shop_id', $shop->id)
            ->with(['customer', 'lastMessage.sender']);

        // Filter search
        if ($this->search) {
            $query->whereHas('customer', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $this->conversations = $query->orderBy('last_message_at', 'desc')->get();

        // Hitung total unread
        $this->totalUnread = $this->conversations->sum(function($conv) {
            return $conv->unreadCount(Auth::id());
        });
    }

    public function updatedSearch()
    {
        $this->loadConversations();
    }

    public function render()
    {
        return view('livewire.seller-chat-list');
    }
}