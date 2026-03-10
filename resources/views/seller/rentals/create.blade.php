@extends('frontend.masterseller')

@section('content')
    <style>
        .create-rental-container {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 0;
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
            color: #ff5722;
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
            border-color: #ff5722;
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
            border-color: #ff8a65;
            background: #fff5f2;
        }

        .checkbox-option input[type="checkbox"]:checked+label {
            border-color: #ff5722;
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #ff5722;
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
            background: #ff5722;
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
            background: #28a745;
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
            background: #218838;
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

    <div class="create-rental-container">
        <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.rentals.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Tambah Paket Sewa
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

            @if ($products->isEmpty())
                <div class="form-card">
                    <div class="empty-state" style="padding: 2rem 1rem;">
                        <i class="fa fa-box-open" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <h3 style="color: #333; margin-bottom: 0.5rem;">Tidak Ada Produk Tersedia</h3>
                        <p style="color: #6c757d; margin-bottom: 1rem;">Semua produk Anda sudah memiliki paket rental atau
                            belum ada produk.</p>
                        <a href="{{ route('seller.products.create') }}" style="color: #007bff; text-decoration: none;">
                            <i class="fa fa-plus"></i> Tambah Produk Baru
                        </a>
                    </div>
                </div>
            @else
<form id="rentalForm" action="{{ route('seller.rentals.store') }}" method="POST">
                    @csrf

                    <!-- Product Selection -->
                    <div class="form-card">
                        <div class="form-card-title">
                            <i class="fa fa-box"></i>
                            Pilih Produk
                        </div>

                        <div class="form-group">
                            <label for="product_id" class="form-label">
                                Produk <span class="required">*</span>
                            </label>
                            <select name="product_id" id="product_id"
                                class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} ({{ $product->code }})
                                        @if ($product->rentals_count > 0)
                                            • {{ $product->rentals_count }} Paket Sewa
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-hint">Pilih produk yang akan disewakan</small>
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
                                    class="form-input @error('price') is-invalid @enderror" value="{{ old('price') }}"
                                    placeholder="50000" min="1000" required>
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
                                    value="{{ old('cycle_value') }}" placeholder="1" min="1" required>
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
                                    value="{{ old('penalties_price') }}" placeholder="25000" min="1000" required>
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
                                    value="{{ old('penalties_cycle_value') }}" placeholder="1" min="1" required>
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
                                <div class="checkbox-option">
                                    <input type="checkbox" name="is_delivery[]" id="pickup" value="pickup"
                                        {{ is_array(old('is_delivery')) && in_array('pickup', old('is_delivery')) ? 'checked' : '' }}>
                                    <label for="pickup">
                                        <div class="icon">
                                            <i class="fa fa-walking"></i>
                                        </div>
                                        <div class="text">Ambil Sendiri</div>
                                    </label>
                                </div>
                                
<div class="checkbox-option {{ !$hasCourier ? 'disabled' : '' }}">

    <input type="checkbox"
        name="is_delivery[]"
        id="delivery"
        value="delivery"
        {{ !$hasCourier ? 'disabled' : '' }}
        {{ is_array(old('is_delivery')) && in_array('delivery', old('is_delivery')) ? 'checked' : '' }}>

    <label for="delivery">
        <div class="icon">
            <i class="fa fa-truck"></i>
        </div>
        <div class="text">Antar</div>
    </label>

</div>


                            </div>
                            @error('is_delivery')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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

                    </div>

                    <!-- Submit -->
                    <button type="submit" class="submit-btn">
                        <i class="fa fa-save"></i>
                        <span>Simpan Paket Sewa</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("rentalForm");
    if (!form) return;

    const STORAGE_KEY = "rentalFormDraft";

    /* =========================
       SAVE FORM (MANUAL)
    ==========================*/
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

    /* =========================
       RESTORE FORM
    ==========================*/
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

    /* =========================
       BUTTON ADD COURIER
    ==========================*/
    const btnCourier = document.getElementById("btnGoAddCourier");

    if (btnCourier) {
        btnCourier.addEventListener("click", function () {
            saveForm(); // SIMPAN SEKALI SAAT KLIK
            window.location.href = "{{ route('seller.couriers.create') }}?from=rental";
        });
    }

    /* =========================
       CLEAR STORAGE AFTER SUBMIT
    ==========================*/
    form.addEventListener("submit", function () {
        localStorage.removeItem(STORAGE_KEY);
    });

});
</script>


@endsection
