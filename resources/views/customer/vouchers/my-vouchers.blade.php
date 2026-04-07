@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/protail.css') }}?v={{ time() }}">


<style>
.vouchers-container {
    padding: 20px 16px;
    background: #f8f9fa;
    min-height: 100vh;
}

.voucher-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 16px;
    position: relative;
}

.voucher-header {
    background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
    padding: 16px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.voucher-name {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
}

.voucher-shop {
    font-size: 13px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
}

.voucher-badge {
    background: rgba(255,255,255,0.25);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.voucher-body {
    padding: 16px;
}

.voucher-discount {
    font-size: 28px;
    font-weight: 700;
    color: #ee4d2d;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.voucher-discount .unit {
    font-size: 18px;
    font-weight: 500;
}

.voucher-code {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 2px dashed #ddd;
}

.voucher-code-text {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    font-size: 16px;
    color: #333;
}

.copy-btn {
    background: #ee4d2d;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.copy-btn:hover {
    background: #d44226;
}

.copy-btn:active {
    transform: scale(0.95);
}

.voucher-details {
    display: grid;
    gap: 8px;
    font-size: 13px;
    color: #666;
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-row i {
    width: 20px;
    color: #ee4d2d;
}

.voucher-footer {
    padding: 12px 16px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}

.usage-info {
    color: #666;
    display: flex;
    align-items: center;
    gap: 6px;
}

.usage-badge {
    background: #ee4d2d;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 11px;
}

.usage-badge.used {
    background: #e74c3c;
}

.claimed-date {
    color: #999;
}

.voucher-expired {
    opacity: 0.6;
}

.voucher-expired .voucher-header {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
}

.voucher-used .voucher-header {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.expired-badge {
    background: #e74c3c;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.used-badge {
    background: rgba(255,255,255,0.9);
    color: #e74c3c;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.empty-state i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 16px;
}

.empty-state h5 {
    color: #666;
    margin-bottom: 8px;
}

.empty-state p {
    color: #999;
    font-size: 14px;
    margin-bottom: 20px;
}

.btn-browse {
    display: inline-block;
    padding: 12px 24px;
    background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-browse:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(238, 77, 45, 0.4);
    color: white;
}

.max-discount-info {
    font-size: 11px;
    color: #999;
    margin-top: 4px;
}
</style>


    {{-- HEADER DETAIL PRODUK --}}
    <div class="product-detail-header">
        <a href="{{ url()->previous() }}"" class="header-back">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="header-title">Voucher Saya</div>
        <div class="header-spacer"></div>
    </div>
<div class="vouchers-container">


    <!-- Vouchers List -->
    @forelse($vouchers as $voucher)
        @php
            $isExpired = $voucher->valid_until && now()->gt($voucher->valid_until);
            $isUsed = $voucher->has_been_used ?? false;
        @endphp

        <div class="voucher-card {{ $isExpired ? 'voucher-expired' : '' }} {{ $isUsed ? 'voucher-used' : '' }}">
            <!-- Header -->
            <div class="voucher-header">
                <div>
                    <div class="voucher-name">{{ $voucher->name }}</div>
                    <div class="voucher-shop">
                        <i class="fa fa-store"></i>
                        {{ $voucher->shop->name_store }}
                    </div>
                </div>
                <div>
                    @if($isUsed)
                        <span class="used-badge">Sudah Digunakan</span>
                    @elseif($isExpired)
                        <span class="expired-badge">Kadaluarsa</span>
                    @else
                        <span class="voucher-badge">Aktif</span>
                    @endif
                </div>
            </div>

            <!-- Body -->
            <div class="voucher-body">
                <!-- Discount Amount -->
                <div class="voucher-discount">
                    @if($voucher->discount_type === 'percentage')
                        <span>{{ $voucher->discount_value }}%</span>
                        <span class="unit">OFF</span>
                    @else
                        <span>Rp {{ number_format($voucher->discount_value, 0, ',', '.') }}</span>
                        <span class="unit">OFF</span>
                    @endif
                </div>

                @if($voucher->discount_type === 'percentage' && $voucher->max_discount)
                    <div class="max-discount-info">
                        Maks. diskon Rp {{ number_format($voucher->max_discount, 0, ',', '.') }}
                    </div>
                @endif

                <!-- Voucher Code -->
                <div class="voucher-code">
                    <span class="voucher-code-text">{{ $voucher->code }}</span>
                    @if(!$isUsed && !$isExpired)
                        <button class="copy-btn" onclick="copyVoucherCode('{{ $voucher->code }}')">
                            <i class="fa fa-copy"></i> Salin
                        </button>
                    @endif
                </div>

                <!-- Details -->
                <div class="voucher-details">
                    <div class="detail-row">
                        <i class="fa fa-wallet"></i>
                        <span>Min. transaksi: <strong>Rp {{ number_format($voucher->min_transaction, 0, ',', '.') }}</strong></span>
                    </div>

                    @if($voucher->valid_from || $voucher->valid_until)
                        <div class="detail-row">
                            <i class="fa fa-calendar"></i>
                            <span>
                                Berlaku: 
                                @if($voucher->valid_from)
                                    {{ \Carbon\Carbon::parse($voucher->valid_from)->format('d/m/Y') }}
                                @else
                                    Sekarang
                                @endif
                                - 
                                @if($voucher->valid_until)
                                    {{ \Carbon\Carbon::parse($voucher->valid_until)->format('d/m/Y') }}
                                @else
                                    Selamanya
                                @endif
                            </span>
                        </div>
                    @endif

                    @if($voucher->description)
                        <div class="detail-row">
                            <i class="fa fa-info-circle"></i>
                            <span>{{ $voucher->description }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="voucher-footer">
                <div class="usage-info">
                    @if($isUsed)
                        <span class="usage-badge used">
                            <i class="fa fa-check-circle"></i> Sudah Digunakan
                        </span>
                    @else
                        <span class="usage-badge">
                            <i class="fa fa-ticket"></i> Belum Digunakan
                        </span>
                    @endif
                </div>
                <div class="claimed-date">
                    Diklaim: {{ \Carbon\Carbon::parse($voucher->pivot->claimed_at)->format('d M Y') }}
                </div>
            </div>
        </div>
    @empty
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fa fa-ticket"></i>
            <h5>Belum Ada Voucher</h5>
            <p>Anda belum mengklaim voucher apapun.<br>Jelajahi toko dan dapatkan voucher menarik!</p>
               <a href="{{ route('home') }}" class="btn-browse">
                <i class="fa fa-compass"></i> Jelajahi Toko
            </a>
        </div>
    @endforelse
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function copyVoucherCode(code) {
    // Copy to clipboard
    navigator.clipboard.writeText(code).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: `Kode voucher "${code}" telah disalin`,
            showConfirmButton: false,
            timer: 1500,
            toast: true,
            position: 'top-end'
        });
    }).catch(() => {
        // Fallback untuk browser lama
        const textarea = document.createElement('textarea');
        textarea.value = code;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: `Kode voucher "${code}" telah disalin`,
            showConfirmButton: false,
            timer: 1500,
            toast: true,
            position: 'top-end'
        });
    });
}
</script>
@endsection