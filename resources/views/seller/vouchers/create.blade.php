@extends('frontend.masterseller')

@section('content')
<style>
.form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.form-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
}

.form-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f3f4f6;
}

.form-header a {
    color: #6b7280;
    font-size: 20px;
}

.form-header h2 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.form-group label .required {
    color: #dc2626;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-help {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.discount-preview {
    background: linear-gradient(135deg, #A20B0B 0%, #770C0C 100%);
    color: white;
    padding: 24px;
    border-radius: 12px;
    text-align: center;
    margin: 20px 0;
}

.discount-preview-value {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 8px;
}

.discount-preview-label {
    font-size: 14px;
    opacity: 0.9;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.checkbox-group input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f3f4f6;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #A20B0B;
    color: white;
    flex: 1;
}

.btn-primary:hover {
    background: #770C0C;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.error {
    color: #dc2626;
    font-size: 12px;
    margin-top: 4px;
}

.input-error {
    border-color: #dc2626 !important;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-danger {
    background-color: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.info-box {
    background: #eff6ff;
    border: 2px solid #bfdbfe;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}

.info-box h4 {
    color: #1e40af;
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 8px 0;
}

.info-box p {
    color: #1e40af;
    font-size: 13px;
    margin: 0;
    line-height: 1.6;
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .form-container {
        padding: 12px;
    }
    
    .form-card {
        padding: 20px;
    }
    
    .discount-preview-value {
        font-size: 36px;
    }
}

</style>

    <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.vouchers.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Buat Voucher Baru
        </div>
        <div class="create-header-spacer"></div>
    </div>

<div class="form-container">
    <div class="form-card">
        <div class="info-box">
            <h4>ℹ️ Informasi Voucher</h4>
            <p>
                • Setiap voucher hanya dapat digunakan <strong>sekali per user</strong><br>
                • User harus mengklaim voucher terlebih dahulu sebelum menggunakan<br>
                • Voucher dapat diklaim dan digunakan selama masa berlaku
            </p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('seller.vouchers.store') }}" method="POST" id="voucherForm">
            @csrf

            <div class="form-group">
                <label>Nama Voucher <span class="required">*</span></label>
                <input 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}"
                    placeholder="Contoh: Diskon Spesial Weekend"
                    class="{{ $errors->has('name') ? 'input-error' : '' }}"
                    required
                >
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Kode Voucher</label>
                <input 
                    type="text" 
                    name="code" 
                    value="{{ old('code') }}"
                    placeholder="Kosongkan untuk generate otomatis"
                    class="{{ $errors->has('code') ? 'input-error' : '' }}"
                    maxlength="50"
                    disabled
                >
                <div class="form-help">Biarkan kosong untuk membuat kode otomatis</div>
                @error('code')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea 
                    name="description" 
                    placeholder="Deskripsi singkat tentang voucher..."
                    class="{{ $errors->has('description') ? 'input-error' : '' }}"
                >{{ old('description') }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tipe Diskon <span class="required">*</span></label>
                    <select 
                        name="discount_type" 
                        id="discountType"
                        class="{{ $errors->has('discount_type') ? 'input-error' : '' }}"
                        required
                    >
                        <option value="percentage" {{ old('discount_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                    </select>
                    @error('discount_type')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label id="discountValueLabel">Nilai Diskon (%) <span class="required">*</span></label>
                    <input 
                        type="number" 
                        name="discount_value" 
                        id="discountValue"
                        value="{{ old('discount_value') }}"
                        placeholder="0"
                        min="1"
                        class="{{ $errors->has('discount_value') ? 'input-error' : '' }}"
                        required
                    >
                    @error('discount_value')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="discount-preview" id="discountPreview">
                <div class="discount-preview-value" id="previewValue">0%</div>
                <div class="discount-preview-label">Diskon</div>
            </div>

            <div class="form-group" id="maxDiscountGroup" style="display: block;">
                <label>Maksimal Diskon (Rp)</label>
                <input 
                    type="number" 
                    name="max_discount" 
                    value="{{ old('max_discount') }}"
                    placeholder="Contoh: 50000"
                    min="1000"
                    step="1000"
                    class="{{ $errors->has('max_discount') ? 'input-error' : '' }}"
                >
                <div class="form-help">Batasan maksimal potongan untuk diskon persentase</div>
                @error('max_discount')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Minimal Transaksi (Rp) <span class="required">*</span></label>
                <input 
                    type="number" 
                    name="min_transaction" 
                    value="{{ old('min_transaction', '0') }}"
                    placeholder="0"
                    min="0"
                    step="1000"
                    class="{{ $errors->has('min_transaction') ? 'input-error' : '' }}"
                    required
                >
                @error('min_transaction')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Tanggal Mulai</label>
                <input 
                    type="datetime-local" 
                    name="valid_from" 
                    value="{{ old('valid_from') }}"
                    class="{{ $errors->has('valid_from') ? 'input-error' : '' }}"
                >
                @error('valid_from')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Tanggal Berakhir</label>
                <input 
                    type="datetime-local" 
                    name="valid_until" 
                    value="{{ old('valid_until') }}"
                    class="{{ $errors->has('valid_until') ? 'input-error' : '' }}"
                >
                @error('valid_until')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input 
                        type="hidden"
                        name="is_active"
                        value="0"
                    >
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        id="isActive"
                        value="1"
                        {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                    >
                    <label for="isActive" style="margin: 0;">Aktifkan voucher sekarang</label>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('seller.vouchers.index') }}" class="btn btn-secondary">
                    <i class="fa fa-times"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check"></i> Simpan Voucher
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountType = document.getElementById('discountType');
    const discountValue = document.getElementById('discountValue');
    const discountValueLabel = document.getElementById('discountValueLabel');
    const maxDiscountGroup = document.getElementById('maxDiscountGroup');
    const previewValue = document.getElementById('previewValue');

    function updateForm() {
        const type = discountType.value;
        const value = parseFloat(discountValue.value) || 0;

        if (type === 'percentage') {
            // Untuk persentase
            discountValueLabel.innerHTML = 'Nilai Diskon (%) <span class="required">*</span>';
            discountValue.placeholder = 'Contoh: 20';
            discountValue.max = 100;
            discountValue.step = 1;
            discountValue.min = 1;
            maxDiscountGroup.style.display = 'block';
            previewValue.textContent = value + '%';
        } else {
            // Untuk nominal tetap (Rupiah)
            discountValueLabel.innerHTML = 'Nilai Diskon (Rp) <span class="required">*</span>';
            discountValue.placeholder = 'Contoh: 20000';
            discountValue.removeAttribute('max');
            discountValue.step = 1000; // Kelipatan 1000 untuk Rupiah
            discountValue.min = 1000;
            maxDiscountGroup.style.display = 'none';
            previewValue.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        }
    }

    discountType.addEventListener('change', updateForm);
    discountValue.addEventListener('input', updateForm);

    // Jalankan saat pertama kali load
    updateForm();
});
</script>

@endsection