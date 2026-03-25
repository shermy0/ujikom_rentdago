@extends('frontend.masterseller')

@section('content')
    <style>
        .edit-rental-container {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 0;
        }

        .edit-header-bar {
            background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .edit-header-back {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .edit-header-back a {
            color: #fff;
            font-size: 20px;
            text-decoration: none;
        }

        .edit-header-title {
            flex: 1;
            text-align: center;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }

        .edit-header-spacer {
            width: 40px;
        }

        .form-section {
            padding: 1rem;
        }

        .form-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .form-card-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-card-title i {
            color: #A20B0B;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .required {
            color: #dc3545;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #A20B0B;
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
        }

        .form-hint {
            display: block;
            margin-top: 0.375rem;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .invalid-feedback {
            display: block;
            margin-top: 0.375rem;
            font-size: 0.8rem;
            color: #dc3545;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .input-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-group .form-input {
            flex: 1;
        }

        .input-addon {
            padding: 0.75rem 1rem;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #6c757d;
            white-space: nowrap;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .checkbox-option {
            position: relative;
        }

        .checkbox-option input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .checkbox-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            position: relative;
            min-height: 120px;
        }

        .checkbox-option label:hover {
            border-color: #A20B0B;
            background: #fff5f2;
        }

        .checkbox-option input[type="checkbox"]:checked+label {
            border-color: #770C0C;
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #770C0C;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.2);
        }

        /* Checkmark indicator */
        .checkbox-option input[type="checkbox"]:checked+label::before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            background: #770C0C;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .checkbox-option label .icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            transition: transform 0.3s;
        }

        .checkbox-option input[type="checkbox"]:checked+label .icon {
            transform: scale(1.1);
        }

        .checkbox-option label .text {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .submit-btn {
            width: 100%;
            padding: 0.875rem;
            background: #A20B0B;
            border: none;
            color: #fff;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 3rem;
        }

        .submit-btn:hover {
            background: #770C0C;
            transform: translateY(-1px);
        }

        .error-alert {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #1565c0;
        }

        .info-box i {
            margin-right: 0.5rem;
        }

        .product-display {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-display img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .product-display-info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
            color: #333;
        }

        .product-display-info p {
            margin: 0;
            font-size: 0.85rem;
            color: #6c757d;
        }

                /* Disabled delivery style */
.checkbox-option.disabled {
    opacity: 0.5;
    pointer-events: none;
}

.checkbox-option.disabled label {
    cursor: not-allowed;
}

.delivery-warning {
    background: #fff3cd;
    color: #856404;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
    </style>

    <div class="edit-rental-container">
        <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.rentals.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Edit Paket Sewa
        </div>
        <div class="create-header-spacer"></div>
    </div>

        <div class="form-section">
            @if ($errors->any())
                <div class="error-alert">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

<form id="rentalForm" action="{{ route('seller.rentals.update', $rental->id) }}" method="POST">                @csrf
                @method('PUT')

                <!-- Product Info -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa fa-box"></i>
                        Informasi Produk
                    </div>

                    <div class="product-display">
                        @if ($rental->product->images->count() > 0)
                            <img src="{{ asset('storage/' . $rental->product->images->first()->image_path) }}"
                                alt="{{ $rental->product->name }}">
                        @else
                            <div
                                style="width: 60px; height: 60px; background: #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-camera" style="color: #999;"></i>
                            </div>
                        @endif
                        <div class="product-display-info">
                            <h4>{{ $rental->product->name }}</h4>
                            <p>{{ $rental->product->code }} • {{ $rental->product->category->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Rental Price -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa fa-tag"></i>
                        Harga Sewa
                    </div>

                    <div class="form-group">
                        <label for="price" class="form-label">
                            Harga Sewa <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-addon">Rp</span>
                            <input type="number" name="price" id="price"
                                class="form-input @error('price') is-invalid @enderror"
                                value="{{ old('price', $rental->price) }}" min="1000" required>
                        </div>
                        @error('price')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="cycle_value" class="form-label">
                            Durasi Sewa <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="cycle_value" id="cycle_value"
                                class="form-input @error('cycle_value') is-invalid @enderror"
                                value="{{ old('cycle_value', $rental->cycle_value) }}" min="1" required>
                            <span class="input-addon">Jam</span>
                        </div>
                        @error('cycle_value')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-hint">Contoh: Rp 50.000 per 1 Jam</small>
                    </div>
                </div>

                <!-- Penalty -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa fa-exclamation-triangle"></i>
                        Denda Keterlambatan
                    </div>

                    <div class="info-box">
                        <i class="fa fa-info-circle"></i>
                        Denda akan dikenakan jika penyewa terlambat mengembalikan produk
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="penalties_price" class="form-label">
                            Harga Denda <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-addon">Rp</span>
                            <input type="number" name="penalties_price" id="penalties_price"
                                class="form-input @error('penalties_price') is-invalid @enderror"
                                value="{{ old('penalties_price', $rental->penalties_price) }}" min="1000" required>
                        </div>
                        @error('penalties_price')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="penalties_cycle_value" class="form-label">
                            Per Keterlambatan <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="penalties_cycle_value" id="penalties_cycle_value"
                                class="form-input @error('penalties_cycle_value') is-invalid @enderror"
                                value="{{ old('penalties_cycle_value', $rental->penalties_cycle_value) }}" min="1"
                                required>
                            <span class="input-addon">Jam</span>
                        </div>
                        @error('penalties_cycle_value')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-hint">Contoh: Denda Rp 25.000 per 1 Jam keterlambatan</small>
                    </div>
                </div>

                <!-- Delivery Method -->
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fa fa-truck"></i>
                        Metode Pengiriman
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Pilih Metode <span class="required">*</span>
                        </label>
                        <small class="form-hint" style="margin-bottom: 1rem; display: block;">
                            <i class="fa fa-info-circle"></i> Anda bisa memilih salah satu atau keduanya
                        </small>
                       <div class="checkbox-group">
    {{-- PICKUP --}}
    <div class="checkbox-option">
        <input type="checkbox" name="is_delivery[]" id="pickup" value="pickup"
            {{ (is_array(old('is_delivery')) && in_array('pickup', old('is_delivery'))) ||
            (!old('is_delivery') && ($rental->is_delivery === 'pickup' || $rental->is_delivery === 'both'))
                ? 'checked'
                : '' }}>
        <label for="pickup">
            <div class="icon">
                <i class="fa fa-walking"></i>
            </div>
            <div class="text">Ambil Sendiri</div>
        </label>
    </div>

    {{-- DELIVERY --}}
    <div class="checkbox-option {{ !$hasCourier ? 'disabled' : '' }}">
        <input type="checkbox"
            name="is_delivery[]"
            id="delivery"
            value="delivery"
            {{ !$hasCourier ? 'disabled' : '' }}
            {{ (is_array(old('is_delivery')) && in_array('delivery', old('is_delivery'))) ||
            (!old('is_delivery') && ($rental->is_delivery === 'delivery' || $rental->is_delivery === 'both'))
                ? 'checked'
                : '' }}>
        <label for="delivery">
            <div class="icon">
                <i class="fa fa-truck"></i>
            </div>
            <div class="text">Antar</div>
        </label>
    </div>
</div>
@if(!$hasCourier)
<div class="delivery-warning">
    <i class="fa fa-info-circle"></i>
    Tambahkan kurir terlebih dahulu untuk mengaktifkan metode antar.

    <button type="button"
        id="btnGoAddCourier"
        style="margin-left:auto;
               background:#856404;
               color:white;
               border:none;
               padding:4px 10px;
               border-radius:6px;
               font-size:0.75rem;
               cursor:pointer;">
        Tambah Kurir
    </button>
</div>
@endif
                        @error('is_delivery')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="submit-btn">
                    <i class="fa fa-save"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </form>
        </div>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("rentalForm");
    if (!form) return;

    const STORAGE_KEY = "rentalEditDraft";

    function saveForm() {
        const data = new FormData(form);
        const obj = {};

        data.forEach((value, key) => {
            if (obj[key]) {
                if (!Array.isArray(obj[key])) {
                    obj[key] = [obj[key]];
                }
                obj[key].push(value);
            } else {
                obj[key] = value;
            }
        });

        localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
    }

    // RESTORE
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
        const data = JSON.parse(saved);

        Object.keys(data).forEach(name => {

            const checkboxes = form.querySelectorAll(`[name="${name}[]"]`);
            if (checkboxes.length) {
                checkboxes.forEach(cb => {
                    if (Array.isArray(data[name])) {
                        cb.checked = data[name].includes(cb.value);
                    } else {
                        cb.checked = cb.value === data[name];
                    }
                });
                return;
            }

            const input = form.querySelector(`[name="${name}"]`);
            if (input) {
                input.value = data[name];
            }
        });
    }

    // BUTTON TAMBAH KURIR
    const btnCourier = document.getElementById("btnGoAddCourier");

    if (btnCourier) {
        btnCourier.addEventListener("click", function () {
            saveForm();
            window.location.href = "{{ route('seller.couriers.create') }}?from=edit_rental&id={{ $rental->id }}";
        });
    }

    // CLEAR STORAGE
    form.addEventListener("submit", function () {
        localStorage.removeItem(STORAGE_KEY);
    });

});
</script>
@endsection
