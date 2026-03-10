<?php
// app/Livewire/SellerChatRoom.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Conversation;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class SellerChatRoom extends Component
{
    public $conversation;
    public $messages = [];
    public $newMessage = '';
    public $customerId;
    public $isTyping = false;

    protected $listeners = ['refreshMessages' => 'loadMessages'];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        
        $shop = Auth::user()->shop;

        if (!$shop) {
            abort(403, 'Anda belum memiliki toko');
        }
        
        $this->conversation = Conversation::where('shop_id', $shop->id)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        $this->loadMessages();
        $this->markMessagesAsRead();
    }

    public function loadMessages()
    {
        $this->messages = ChatMessage::where('conversation_id', $this->conversation->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        $this->checkTypingStatus();
    }

    public function sendMessage()
    {
        if (trim($this->newMessage) === '') {
            return;
        }

        ChatMessage::create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => Auth::id(),
            'message' => $this->newMessage,
        ]);

        $this->conversation->update([
            'last_message_at' => now()
        ]);

        $this->stopTyping();

        $this->newMessage = '';
        $this->loadMessages();
    }

    public function typing()
    {
        cache()->put(
            "typing_seller_{$this->conversation->id}",
            now(),
            now()->addSeconds(5)
        );
    }

    public function stopTyping()
    {
        cache()->forget("typing_seller_{$this->conversation->id}");
    }

    public function checkTypingStatus()
    {
        $customerTyping = cache()->get("typing_customer_{$this->conversation->id}");
        
        if ($customerTyping && $customerTyping->diffInSeconds(now()) < 3) {
            $this->isTyping = true;
        } else {
            $this->isTyping = false;
        }
    }

    public function markMessagesAsRead()
    {
        ChatMessage::where('conversation_id', $this->conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function render()
    {
        return view('livewire.seller-chat-room');
    }
}