@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/checkout.css') }}?v={{ time() }}">
<style>
.rental-options-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.rental-option-card {
    cursor: pointer;
    position: relative;
    margin: 0;
}
.rental-radio-input {
    position: absolute;
    opacity: 0;
}
.rental-option-content {
    border: 2px solid #eee;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    transition: all 0.2s ease;
    background: #fff;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.rental-cycle {
    display: block;
    font-weight: 700;
    font-size: 15px;
    color: #2b2b2b;
    margin-bottom: 4px;
}
.rental-price {
    display: block;
    font-size: 13px;
    color: #ff6b35;
    font-weight: 600;
}
.rental-radio-input:checked + .rental-option-content {
    border-color: #ff6b35;
    background: #fff9f6;
    box-shadow: 0 4px 10px rgba(255, 107, 53, 0.1);
}
.rental-radio-input:hover:not(:checked) + .rental-option-content {
    border-color: #ffdacc;
}
</style>

<div class="checkout-container" data-shop-id="{{ $product->shop_id }}">
    
    {{-- HEADER --}}
    <div class="checkout-header">
       <a href="{{ route('customer.product.detail', [
    'slug' => !empty($product->shop?->slug) ? $product->shop->slug : 'no-shop',
    'product' => $product->id
]) }}" class="header-back">
    <i class="fa fa-arrow-left"></i>
</a>

        <div class="header-title">Checkout</div>
        <div style="width:40px;"></div>
    </div>

    {{-- FORM KE CUSTOMER ORDER STORE YANG SUDAH ADA --}}
    <form method="POST" action="{{ route('customer.order.store', $product->id) }}" id="checkoutForm">
        @csrf

        {{-- PRODUCT SUMMARY --}}
        <div class="checkout-card">
            <div class="card-header">
                <i class="fa fa-box"></i>
                <span>Produk yang Disewa</span>
            </div>
            <div class="product-summary">
                <div class="product-image">
                    @if($product->images->count())
                        <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
                    @else
                        <div class="product-placeholder">
                            <i class="fa-solid fa-box"></i>
                        </div>
                    @endif
                </div>
                <div class="product-info">
                    <div class="product-name">{{ $product->name }}</div>
                    <div class="product-category">{{ $product->category->name }}</div>
                    <div class="shop-name">
                        <i class="fa fa-store"></i>
                        {{ $product->shop->name_store }}
                    </div>
                </div>
            </div>
        </div>

        {{-- PILIH PAKET --}}
        <div class="checkout-card">
            <div class="card-header" style="margin-bottom: 12px;">
                <i class="fa fa-clock"></i>
                <span>Pilih Paket Sewa</span>
            </div>
            <div class="rental-options-grid">
                @foreach($product->rentals as $rental)
                    <label class="rental-option-card">
                        <input type="radio" name="product_rental_id" class="rental-radio-input" value="{{ $rental->id }}" required
                            data-cycle="{{ $rental->cycle_value }}" 
                            data-price="{{ $rental->price }}" 
                            data-delivery="{{ is_array($rental->is_delivery) ? implode(',', $rental->is_delivery) : $rental->is_delivery }}" 
                            data-pickup-address="{{ $rental->pickup_address ?? '' }}" 
                            data-penalty="{{ $rental->penalties_price }}">
                        <div class="rental-option-content">
                            <span class="rental-cycle">{{ $rental->cycle_value }} Jam</span>
                            <span class="rental-price">Rp {{ number_format($rental->price, 0, ',', '.') }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- TANGGAL & WAKTU --}}
        <div class="checkout-card" id="startTimeCard" style="display:none;">
            <div class="card-header">
                <i class="fa fa-calendar"></i>
                <span>Tanggal & Jam Mulai</span>
            </div>
            <input type="datetime-local" id="startTime" name="start_time" required>
            <small class="text-hint" id="timeHint" style="display:none;"></small>
        </div>

        {{-- METODE PENGAMBILAN --}}
        <div class="checkout-card" id="deliveryCard" style="display:none;">
            <div class="card-header">
                <i class="fa fa-truck"></i>
                <span>Metode Pengambilan</span>
            </div>
            <select name="delivery_method" id="deliveryMethodSelect" required>
                <option value="">Pilih metode</option>
            </select>
            <small class="text-hint" id="pickupAddressText" style="display:none;"></small>
        </div>

        {{-- ALAMAT PICKUP --}}
        <div class="checkout-card" id="pickupAddressCard" style="display:none;">
            <div class="card-header">
                <i class="fa fa-location-dot"></i>
                <span>Alamat Penjemputan</span>
            </div>
            <select name="pickup_address_id" id="pickupAddressSelect">
                @forelse($addresses as $address)
                    <option value="{{ $address->id }}" {{ $loop->first ? 'selected' : '' }}>
                        {{ $address->label }} – {{ $address->receiver_name }} – {{ $address->receiver_phone }} – {{ $address->address }} {{ $address->notes ? '('.$address->notes.')' : '' }}
                    </option>
                @empty
                    <option value="">Belum ada alamat</option>
                @endforelse
            </select>
            @if($addresses->isEmpty())
                <button type="button" class="btn-add-address" onclick="goToAddAddress()">
                    <i class="fa fa-plus"></i>
                    Tambah Alamat Baru
                </button>
            @endif
        </div>

        {{-- ALAMAT DELIVERY --}}
        <div class="checkout-card" id="deliveryAddressCard" style="display:none;">
            <div class="card-header">
                <i class="fa fa-location-dot"></i>
                <span>Alamat Pengiriman</span>
            </div>
            <select name="user_address_id" id="deliveryAddressSelect">
                @forelse($addresses as $address)
                    <option value="{{ $address->id }}" {{ $loop->first ? 'selected' : '' }}>
                        {{ $address->label }} – {{ $address->receiver_name }} – {{ $address->receiver_phone }} – {{ $address->address }} {{ $address->notes ? '('.$address->notes.')' : '' }}
                    </option>
                @empty
                    <option value="">Belum ada alamat</option>
                @endforelse
            </select>
            @if($addresses->isEmpty())
                <button type="button" class="btn-add-address" onclick="goToAddAddress()">
                    <i class="fa fa-plus"></i>
                    Tambah Alamat Baru
                </button>
            @endif
        </div>

        {{-- VOUCHER --}}
        <div class="checkout-card" id="voucherCard" style="display:none;">
            <div class="card-header">
                <i class="fa fa-ticket"></i>
                <span>Voucher (Opsional)</span>
            </div>
            <select id="voucherSelect" name="voucher_id">
                <option value="">Pilih voucher</option>
            </select>
            <div id="voucherResult" style="display: none;">
                <div id="voucherSuccess" class="voucher-message success" style="display: none;">
                    <i class="fa fa-check-circle"></i>
                    <span id="voucherSuccessText"></span>
                </div>
                <div id="voucherError" class="voucher-message error" style="display: none;">
                    <i class="fa fa-exclamation-circle"></i>
                    <span id="voucherErrorText"></span>
                </div>
            </div>
            <a href="{{ route('customer.vouchers.my') }}" class="link-vouchers" target="_blank">
                <i class="fa fa-eye"></i> Lihat semua voucher saya
            </a>
        </div>

        {{-- RINGKASAN PEMBAYARAN --}}
        <div class="checkout-card" id="summaryCard" style="display:none;">
            <div class="card-header">
                <i class="fa fa-file-invoice"></i>
                <span>Ringkasan Pembayaran</span>
            </div>
            <div class="payment-summary">
                <div class="summary-row" id="originalPriceRow" style="display:none;">
                    <span>Harga Asli</span>
                    <strong id="originalPriceText" style="text-decoration: line-through; color: #999;">-</strong>
                </div>
                <div class="summary-row" id="discountRow" style="display:none;">
                    <span>Diskon</span>
                    <strong id="discountText" style="color: #28a745;">-</strong>
                </div>
                <div class="summary-row">
                    <span>Waktu Selesai</span>
                    <strong id="endTimeText">-</strong>
                </div>
                <div class="summary-row total">
                    <span>Total Harga</span>
                    <strong id="priceText">-</strong>
                </div>
                <div class="summary-penalty">
                    <i class="fa fa-exclamation-triangle"></i>
                    Denda keterlambatan: <span id="penaltyText">-</span> / jam
                </div>
            </div>
        </div>

        {{-- SUBMIT BUTTON --}}
        <div class="checkout-footer">
            <button type="submit" class="btn-checkout" id="btnCheckout" disabled>
                <i class="fa fa-calendar-check"></i>
                Sewa Sekarang
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ========== CHECKOUT PAGE SCRIPT ==========

/* ELEMENTS */
const rentalRadios = document.querySelectorAll('.rental-radio-input');
const startTimeInput = document.getElementById('startTime');
const startTimeCard = document.getElementById('startTimeCard');
const deliveryCard = document.getElementById('deliveryCard');
const deliverySelect = document.getElementById('deliveryMethodSelect');
const pickupAddressText = document.getElementById('pickupAddressText');
const pickupAddressCard = document.getElementById('pickupAddressCard');
const pickupAddressSelect = document.getElementById('pickupAddressSelect');
const deliveryAddressCard = document.getElementById('deliveryAddressCard');
const deliveryAddressSelect = document.getElementById('deliveryAddressSelect');
const voucherCard = document.getElementById('voucherCard');
const voucherSelect = document.getElementById('voucherSelect');
const summaryCard = document.getElementById('summaryCard');
const btnCheckout = document.getElementById('btnCheckout');

const endTimeText = document.getElementById('endTimeText');
const priceText = document.getElementById('priceText');
const penaltyText = document.getElementById('penaltyText');
const originalPriceText = document.getElementById('originalPriceText');
const discountText = document.getElementById('discountText');
const originalPriceRow = document.getElementById('originalPriceRow');
const discountRow = document.getElementById('discountRow');

let appliedVoucher = null;
let currentPrice = 0;
let userVouchers = [];

/* LOAD USER VOUCHERS */
async function loadUserVouchers() {
    try {
        const shopId = document.querySelector('[data-shop-id]')?.dataset.shopId;
        if (!shopId) return;

        const response = await fetch(`/customer/vouchers/available?shop_id=${shopId}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        
        if (data.success && data.vouchers) {
            userVouchers = data.vouchers;
            updateVoucherDropdown();
        }
    } catch (error) {
        console.error('Failed to load vouchers:', error);
    }
}

/* UPDATE VOUCHER DROPDOWN */
function updateVoucherDropdown() {
    voucherSelect.innerHTML = '<option value="">Pilih voucher</option>';
    
    if (userVouchers.length === 0) {
        const noVoucherOption = document.createElement('option');
        noVoucherOption.value = '';
        noVoucherOption.textContent = 'Belum ada voucher tersedia';
        noVoucherOption.disabled = true;
        voucherSelect.appendChild(noVoucherOption);
        return;
    }
    
    userVouchers.forEach(voucher => {
        const option = document.createElement('option');
        option.value = voucher.id;
        
        let displayText = `${voucher.name}`;
        
        if (voucher.discount_type === 'percentage') {
            displayText += ` - ${voucher.discount_value}%`;
            if (voucher.max_discount) {
                displayText += ` (Max Rp ${new Intl.NumberFormat('id-ID').format(voucher.max_discount)})`;
            }
        } else {
            displayText += ` - Potongan Rp ${new Intl.NumberFormat('id-ID').format(voucher.discount_value)}`;
        }
        
        option.textContent = displayText;
        option.dataset.voucherId = voucher.id;
        option.dataset.voucherName = voucher.name;
        option.dataset.discountType = voucher.discount_type;
        option.dataset.discountValue = voucher.discount_value;
        option.dataset.maxDiscount = voucher.max_discount || 0;
        option.dataset.minTransaction = voucher.min_transaction_amount || 0;
        
        voucherSelect.appendChild(option);
    });
}

/* HANDLE VOUCHER SELECTION */
voucherSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (!selectedOption.value) {
        removeVoucher();
        return;
    }

    const minTransaction = parseInt(selectedOption.dataset.minTransaction) || 0;
    
    if (currentPrice < minTransaction) {
        showVoucherError(`Minimal transaksi untuk voucher ini adalah Rp ${new Intl.NumberFormat('id-ID').format(minTransaction)}`);
        this.value = '';
        return;
    }

    const discountType = selectedOption.dataset.discountType;
    const discountValue = parseInt(selectedOption.dataset.discountValue);
    const maxDiscount = parseInt(selectedOption.dataset.maxDiscount) || 0;
    
    let discountAmount = 0;
    if (discountType === 'percentage') {
        discountAmount = Math.floor((currentPrice * discountValue) / 100);
        if (maxDiscount > 0 && discountAmount > maxDiscount) {
            discountAmount = maxDiscount;
        }
    } else {
        discountAmount = Math.min(discountValue, currentPrice);
    }

    appliedVoucher = {
        id: selectedOption.dataset.voucherId,
        name: selectedOption.dataset.voucherName,
        discount_amount: discountAmount
    };

    showVoucherSuccess(`Voucher "${appliedVoucher.name}" diterapkan! Hemat Rp ${new Intl.NumberFormat('id-ID').format(appliedVoucher.discount_amount)}`);
    calculateRent();
});

/* REMOVE VOUCHER */
function removeVoucher() {
    appliedVoucher = null;
    voucherSelect.value = '';
    document.getElementById('voucherResult').style.display = 'none';
    calculateRent();
}

/* SHOW VOUCHER SUCCESS */
function showVoucherSuccess(message) {
    const resultDiv = document.getElementById('voucherResult');
    const successDiv = document.getElementById('voucherSuccess');
    const errorDiv = document.getElementById('voucherError');
    
    resultDiv.style.display = 'block';
    successDiv.style.display = 'flex';
    errorDiv.style.display = 'none';
    document.getElementById('voucherSuccessText').innerText = message;
}

/* SHOW VOUCHER ERROR */
function showVoucherError(message) {
    const resultDiv = document.getElementById('voucherResult');
    const successDiv = document.getElementById('voucherSuccess');
    const errorDiv = document.getElementById('voucherError');
    
    resultDiv.style.display = 'block';
    successDiv.style.display = 'none';
    errorDiv.style.display = 'flex';
    document.getElementById('voucherErrorText').innerText = message;
}

/* CALCULATE RENT */
function calculateRent() {
    const selected = document.querySelector('.rental-radio-input:checked');
    if (!selected || !selected.dataset.cycle) return;

    const cycle = parseInt(selected.dataset.cycle);
    const price = parseInt(selected.dataset.price);
    const penalty = parseInt(selected.dataset.penalty);
    const startTime = startTimeInput.value;

    if (!startTime) return;

    currentPrice = price;

    const start = new Date(startTime);
    const end = new Date(start.getTime() + cycle * 60 * 60 * 1000);

    endTimeText.innerText = formatTanggalJam(end);
    penaltyText.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(penalty);

    if (appliedVoucher) {
        const discount = appliedVoucher.discount_amount;
        const finalPrice = price - discount;

        originalPriceRow.style.display = 'flex';
        discountRow.style.display = 'flex';
        originalPriceText.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        discountText.innerText = '- Rp ' + new Intl.NumberFormat('id-ID').format(discount);
        priceText.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(finalPrice);
    } else {
        originalPriceRow.style.display = 'none';
        discountRow.style.display = 'none';
        priceText.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
    }

    summaryCard.style.display = 'block';
    checkFormComplete();
}

/* HANDLE RENTAL SELECT */
rentalRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        const selected = this;

        startTimeCard.style.display = 'none';
    deliveryCard.style.display = 'none';
    pickupAddressCard.style.display = 'none';
    deliveryAddressCard.style.display = 'none';
    voucherCard.style.display = 'none';
    summaryCard.style.display = 'none';
    
    startTimeInput.value = '';
    deliverySelect.innerHTML = '<option value="">Pilih metode</option>';
    removeVoucher();
    btnCheckout.disabled = true;

    if (!selected || !selected.value) return;

    startTimeCard.style.display = 'block';
    voucherCard.style.display = 'block';
    loadUserVouchers();

    const deliveryRaw = selected.dataset.delivery;
    const pickupAddress = selected.dataset.pickupAddress;

    if (!deliveryRaw) return;

    const methods = deliveryRaw.split(',').map(m => m.trim());
    
    methods.forEach(method => {
        if (method === 'pickup') {
            deliverySelect.innerHTML += `<option value="pickup">Pickup</option>`;
        }
        if (method === 'delivery') {
            deliverySelect.innerHTML += `<option value="delivery">Delivery</option>`;
        }
    });

    if (methods.includes('pickup') && pickupAddress) {
        pickupAddressText.innerText = 'Alamat Pickup: ' + pickupAddress;
        pickupAddressText.style.display = 'block';
    }

    deliveryCard.style.display = 'block';

    if (deliverySelect.options.length === 2) {
        deliverySelect.selectedIndex = 1;
        handleDeliveryMethodChange();
    }
    });
});

startTimeInput.addEventListener('change', function() {
    calculateRent();
    checkFormComplete();
});

/* HANDLE DELIVERY METHOD */
function handleDeliveryMethodChange() {
    const method = deliverySelect.value;

    if (method === 'delivery') {
        deliveryAddressCard.style.display = 'block';
        deliveryAddressSelect.required = true;
        
        pickupAddressCard.style.display = 'none';
        pickupAddressSelect.required = false;
        
        selectFirstOption(deliveryAddressSelect);
    } 
    else if (method === 'pickup') {
        pickupAddressCard.style.display = 'block';
        pickupAddressSelect.required = true;
        
        deliveryAddressCard.style.display = 'none';
        deliveryAddressSelect.required = false;
        
        selectFirstOption(pickupAddressSelect);
    } 
    else {
        deliveryAddressCard.style.display = 'none';
        deliveryAddressSelect.required = false;
        
        pickupAddressCard.style.display = 'none';
        pickupAddressSelect.required = false;
    }
    
    checkFormComplete();
}

deliverySelect.addEventListener('change', handleDeliveryMethodChange);

/* CHECK FORM COMPLETE */
function checkFormComplete() {
    const selectedRental = document.querySelector('.rental-radio-input:checked');
    const hasRental = selectedRental && selectedRental.value !== '';
    const hasStartTime = startTimeInput.value !== '';
    const hasDeliveryMethod = deliverySelect.value !== '';
    
    let hasAddress = false;
    if (deliverySelect.value === 'delivery') {
        hasAddress = deliveryAddressSelect.value !== '';
    } else if (deliverySelect.value === 'pickup') {
        hasAddress = pickupAddressSelect.value !== '';
    }
    
    btnCheckout.disabled = !(hasRental && hasStartTime && hasDeliveryMethod && hasAddress);
}

/* UTILITY FUNCTIONS */
function selectFirstOption(selectEl) {
    if (!selectEl) return;
    if (selectEl.options.length > 0) {
        selectEl.selectedIndex = 0;
    }
}

function formatTanggalJam(date) {
    const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return `${date.getDate()} ${bulan[date.getMonth()]} ${date.getFullYear()}, ${String(date.getHours()).padStart(2,'0')}.${String(date.getMinutes()).padStart(2,'0')}`;
}

function goToAddAddress() {
    const selectedRental = document.querySelector('.rental-radio-input:checked');
    const rentState = {
        product_rental_id: selectedRental ? selectedRental.value : '',
        start_time: startTimeInput.value,
        delivery_method: deliverySelect.value
    };

    sessionStorage.setItem('rent_state', JSON.stringify(rentState));

    const pathParts = window.location.pathname.split('/');
    const productId = pathParts[pathParts.length - 1];

    window.location.href =
        `/customer/addresses/create` +
        `?from=rent` +
        `&product_id=${productId}` +
        `&product_rental_id=${rentState.product_rental_id}` +
        `&start_time=${rentState.start_time}` +
        `&delivery_method=${rentState.delivery_method}`;
}


/* RESTORE STATE FROM SESSION */
document.addEventListener('DOMContentLoaded', function() {
    const savedState = sessionStorage.getItem('rent_state');

    if (!savedState) return;

    const state = JSON.parse(savedState);

    if (state.product_rental_id) {
        const radioToSelect = document.querySelector(`.rental-radio-input[value="${state.product_rental_id}"]`);
        if (radioToSelect) {
            radioToSelect.checked = true;
            radioToSelect.dispatchEvent(new Event('change'));
        }
    }

    setTimeout(() => {
        if (state.start_time) {
            startTimeInput.value = state.start_time;
            startTimeInput.dispatchEvent(new Event('change'));
        }

        if (state.delivery_method) {
            deliverySelect.value = state.delivery_method;
            deliverySelect.dispatchEvent(new Event('change'));
        }
    }, 300);

    sessionStorage.removeItem('rent_state');
});

/* FORM SUBMIT HANDLING */
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const btn = btnCheckout;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
    }
});
</script>

{{-- SWEETALERT DARI BACKEND --}}
@if(session('rent_conflict'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Waktu Tidak Tersedia',
    text: 'Tanggal dan jam yang kamu pilih sedang disewa. Silakan pilih waktu lain.'
});
</script>
@endif

@if(session('invalid_start_time'))
<script>
Swal.fire({
    icon: 'warning',
    title: 'Waktu Tidak Valid',
    text: 'Waktu mulai tidak boleh kurang dari waktu sekarang. Silakan pilih ulang.'
});
</script>
@endif

@if ($errors->any())
<script>
Swal.fire({
    icon: 'error',
    title: 'Validasi Error',
    html: '<ul style="text-align: left;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>'
});
</script>
@endif

@endsection