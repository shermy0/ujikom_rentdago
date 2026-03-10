<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class CustomerChatList extends Component
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
        $query = Conversation::where('customer_id', Auth::id())
            ->with(['shop', 'lastMessage.sender']);

        // Filter search
        if ($this->search) {
            $query->whereHas('shop', function($q) {
                $q->where('name_store', 'like', '%' . $this->search . '%');
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
        return view('livewire.customer-chat-list');
    }
}