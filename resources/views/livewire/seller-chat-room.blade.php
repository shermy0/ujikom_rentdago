{{-- resources/views/livewire/seller-chat-room.blade.php --}}

<div class="chat-room" wire:poll.2s="loadMessages">
    
    {{-- HEADER --}}
    <div class="chat-header chat-room-header">
        <a href="{{ route('seller.chat.index') }}" class="back-btn">
            <i class="fa fa-arrow-left"></i>
        </a>

        <div class="chat-header-info">
            
            <div class="chat-avatar small">
                @if($conversation->customer->avatar)
                    <img 
                        src="{{ asset('storage/' . $conversation->customer->avatar) }}" 
                        alt="{{ $conversation->customer->name }}"
                    >
                @else
                    <span>{{ substr($conversation->customer->name, 0, 2) }}</span>
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
                <div class="fw-semibold">{{ $conversation->customer->name }}</div>
                <small class="text-muted">
                    @if($isTyping)
                        <span class="text-primary">● sedang mengetik...</span>
                    @else
                        {{ $conversation->customer->phone }}
                    @endif
                </small>
            </div>
        </div>
    </div>

    {{-- CHAT BODY --}}
    <div class="chat-body" id="chatBody">
        @foreach($messages as $message)
            {{-- Seller = biru (customer class), Customer = putih (shop class) --}}
            <div class="chat-bubble {{ $message->sender_id == Auth::id() ? 'customer' : 'shop' }}">
                {{ $message->message }}
                <small class="chat-time">{{ $message->created_at->format('H:i') }}</small>
            </div>
        @endforeach

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

    setInterval(() => {
        const chatBody = document.getElementById('chatBody');
        const currentHeight = chatBody.scrollHeight;
        
        if (currentHeight !== previousHeight) {
            chatBody.scrollTop = chatBody.scrollHeight;
            previousHeight = currentHeight;
        }
    }, 500);

    document.addEventListener('DOMContentLoaded', () => {
        const chatBody = document.getElementById('chatBody');
        chatBody.scrollTop = chatBody.scrollHeight;
        previousHeight = chatBody.scrollHeight;
    });
</script>
