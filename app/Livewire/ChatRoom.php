<?php
// app/Livewire/ChatRoom.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Conversation;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatRoom extends Component
{
    public $conversation;
    public $messages = [];
    public $newMessage = '';
    public $shopId;
    public $isTyping = false;
    public $lastTypingTime = 0;

    protected $listeners = ['refreshMessages' => 'loadMessages'];

    public function mount($shopId)
    {
        $this->shopId = $shopId;
        
        $shop = \App\Models\Shop::findOrFail($shopId);
        
        $this->conversation = Conversation::firstOrCreate([
            'customer_id' => Auth::id(),
            'shop_id' => $shop->id,
        ]);

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

        $message = ChatMessage::create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => Auth::id(),
            'message' => $this->newMessage,
        ]);

        $this->conversation->update([
            'last_message_at' => now()
        ]);

        // 🔔 Kirim notifikasi ke seller
        $this->notifySellerNewMessage($message);

        $this->stopTyping();
        $this->newMessage = '';
        $this->loadMessages();
        
        $this->dispatch('scrollToBottom');
    }

    /**
     * 🔔 Notifikasi pesan baru ke seller
     */
    private function notifySellerNewMessage($message)
    {
        try {
            $shop = $this->conversation->shop()->with('user')->first();
            
            if (!$shop || !$shop->user || !$shop->user->phone) {
                Log::warning('Shop owner phone not found for notification', [
                    'shop_id' => $this->shopId
                ]);
                return;
            }

            $customer = Auth::user();
            $phone = $shop->user->phone;

            // Format nomor telepon
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            $waMessage = "*💬 PESAN BARU DARI CUSTOMER*\n\n";
            $waMessage .= "Halo *{$shop->user->name}*,\n\n";
            $waMessage .= "Anda mendapat pesan baru dari customer:\n\n";
            $waMessage .= "━━━━━━━━━━━━━━━━━━━━\n";
            $waMessage .= "👤 Customer: *{$customer->name}*\n";
            $waMessage .= "📱 Phone: {$customer->phone}\n";
            $waMessage .= "🏪 Toko: *{$shop->name_store}*\n";
            $waMessage .= "━━━━━━━━━━━━━━━━━━━━\n\n";
            $waMessage .= "💬 *Pesan:*\n";
            $waMessage .= "\"{$message->message}\"\n\n";
            $waMessage .= "━━━━━━━━━━━━━━━━━━━━\n\n";
            $waMessage .= "Balas pesan customer di:\n";
            $waMessage .= route('seller.chat.show', $customer->id) . "\n\n";
            $waMessage .= "⏰ " . now()->format('d/m/Y H:i') . "\n";

            kirimwa($phone, $waMessage);

            Log::info('New message notification sent to seller', [
                'shop_id' => $shop->id,
                'customer_id' => $customer->id,
                'message_id' => $message->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send message notification to seller', [
                'error' => $e->getMessage(),
                'shop_id' => $this->shopId
            ]);
        }
    }

    public function typing()
    {
        cache()->put(
            "typing_customer_{$this->conversation->id}",
            now(),
            now()->addSeconds(5)
        );
    }

    public function stopTyping()
    {
        cache()->forget("typing_customer_{$this->conversation->id}");
    }

    public function checkTypingStatus()
    {
        $sellerTyping = cache()->get("typing_seller_{$this->conversation->id}");
        
        if ($sellerTyping && $sellerTyping->diffInSeconds(now()) < 3) {
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
        return view('livewire.chat-room');
    }
}