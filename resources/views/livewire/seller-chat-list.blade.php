<div wire:poll.3s="loadConversations">

    {{-- HEADER dengan Badge Notifikasi --}}
    <div class="chat-header">
        <h6 class="mb-0">
            Chat Customer
            @if($totalUnread > 0)
                <span class="badge bg-danger rounded-pill ms-2">{{ $totalUnread }}</span>
            @endif
        </h6>
    </div>

    {{-- SEARCH --}}
    <div class="home-section">
        <div class="search-box">
            <div class="search-input-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input
                    type="text"
                    wire:model.live="search"
                    class="form-control search-input"
                    placeholder="Cari percakapan customer"
                >
            </div>
        </div>
    </div>

    {{-- LIST CHAT --}}
    @forelse($conversations as $conversation)
        <a href="{{ route('seller.chat.show', $conversation->customer_id) }}" class="chat-item {{ $conversation->unreadCount(Auth::id()) > 0 ? 'unread' : '' }}">
           {{-- Di Seller Chat List --}}
<div class="chat-avatar" style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); flex-shrink: 0;">
    @if($conversation->customer->avatar)
        <img
            src="{{ asset('storage/' . $conversation->customer->avatar) }}"
            alt="{{ $conversation->customer->name }}"
            style="width: 100%; height: 100%; object-fit: cover; display: block;"
        >
    @else
        <span style="font-size: 16px; font-weight: 700; color: white; text-transform: uppercase;">
            {{ substr($conversation->customer->name, 0, 2) }}
        </span>
    @endif
</div>

            <div class="chat-info">
                <div class="chat-top">
                    <span class="chat-name">{{ $conversation->customer->name }}</span>
                    <small class="chat-time">
                        {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : '-' }}
                    </small>
                </div>
                <div class="chat-preview {{ $conversation->lastMessage && $conversation->lastMessage->sender_id != Auth::id() ? '' : 'text-muted' }}">
                    @if($conversation->lastMessage)
                        {{ $conversation->lastMessage->sender_id == Auth::id() ? 'Kamu: ' : '' }}
                        {{ Str::limit($conversation->lastMessage->message, 50) }}

                        {{-- Badge NEW untuk pesan belum dibaca --}}
                        @if($conversation->lastMessage->sender_id != Auth::id() && !$conversation->lastMessage->is_read)
                            <span class="badge bg-primary ms-1">|NEW CHAT</span>
                        @endif
                    @else
                        Belum ada pesan
                    @endif
                </div>
            </div>

            {{-- Badge jumlah unread --}}
            @if($conversation->unreadCount(Auth::id()) > 0)
                <span class="badge bg-danger rounded-pill">{{ $conversation->unreadCount(Auth::id()) }}</span>
            @endif
        </a>
    @empty
        <div class="text-center py-5">
            <i class="fa fa-comments fa-3x text-muted mb-3"></i>
            <p class="text-muted">
                @if($search)
                    Tidak ada hasil untuk "{{ $search }}"
                @else
                    Belum ada percakapan dari customer
                @endif
            </p>
        </div>
    @endforelse

</div>
