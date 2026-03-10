{{-- resources/views/livewire/chat-room.blade.php --}}

<div class="chat-room" wire:poll.2s="loadMessages">
    
    {{-- HEADER --}}
    <div class="chat-header chat-room-header">
        <a href="{{ route('customer.chat.index') }}" class="back-btn">
            <i class="fa fa-arrow-left"></i>
        </a>

        <div class="chat-header-info">
            <div class="chat-avatar small">
                @if($conversation->shop->logo)
                    <img 
                        src="{{ asset('storage/' . $conversation->shop->logo) }}" 
                        alt="{{ $conversation->shop->name_store }}"
                    >
                @else
                    <span>
                        {{ strtoupper(substr($conversation->shop->name_store, 0, 2)) }}
                    </span>
                @endif
            </div>
<style>
    .chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #e5e7eb; /* fallback abu-abu */
    flex-shrink: 0;
}

.chat-avatar.small {
    width: 32px;
    height: 32px;
}

.chat-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.chat-avatar span {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
}

</style>
            <div>
                <div class="fw-semibold">{{ $conversation->shop->name_store }}</div>
                <small class="text-muted" id="typingStatus">
                    @if($isTyping)
                        <span class="text-primary">● sedang mengetik...</span>
                    @else
                        Online
                    @endif
                </small>
            </div>
        </div>

        <a href="{{ route('customer.shop.profile', $conversation->shop->slug) }}"
           class="visit-shop-btn">
            <i class="fa fa-store"></i>
        </a>
    </div>

    {{-- CHAT BODY --}}
    <div class="chat-body" id="chatBody">
        @foreach($messages as $message)
            <div class="chat-bubble {{ $message->sender_id == Auth::id() ? 'customer' : 'shop' }}">
                {{ $message->message }}
                <small class="chat-time">{{ $message->created_at->format('H:i') }}</small>
            </div>
        @endforeach

        {{-- Typing indicator --}}
        @if($isTyping)
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        @endif
    </div>

    {{-- INPUT --}}
    <form wire:submit.prevent="sendMessage" class="chat-input">
        <input
            type="text"
            placeholder="Tulis pesan..."
            wire:model="newMessage"
            wire:keydown="typing"
            wire:blur="stopTyping"
        >

        <button type="submit" style="width: 50px; height: 50px">
            <i class="fa fa-paper-plane" style="font-size:18px"></i>
        </button>
    </form>

</div>


<script>
    let previousHeight = 0;

    // Auto scroll ke bottom kalau ada pesan baru
    setInterval(() => {
        const chatBody = document.getElementById('chatBody');
        const currentHeight = chatBody.scrollHeight;
        
        if (currentHeight !== previousHeight) {
            chatBody.scrollTop = chatBody.scrollHeight;
            previousHeight = currentHeight;
        }
    }, 500);

    // Scroll to bottom saat pertama load
    document.addEventListener('DOMContentLoaded', () => {
        const chatBody = document.getElementById('chatBody');
        chatBody.scrollTop = chatBody.scrollHeight;
        previousHeight = chatBody.scrollHeight;
    });
</script>
