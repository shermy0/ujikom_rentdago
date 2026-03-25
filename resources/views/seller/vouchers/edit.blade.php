@extends('frontend.masterseller')

@section('content')
<style>
* {
    box-sizing: border-box;
}

.voucher-edit-container {
    padding: 24px 16px;
    max-width: 900px;
    margin: 0 auto;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
    transition: color 0.3s;
}

.back-button:hover {
    color: #A20B0B;
}

.edit-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #A20B0B 0%, #770C0C 100%);
    color: white;
    padding: 32px;
}

.card-header h1 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
}

.card-body {
    padding: 32px;
}

.form-section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #A20B0B;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.form-label .required {
    color: #dc2626;
    margin-left: 4px;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #A20B0B;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.form-help {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-top: 6px;
}

.error-message {
    display: block;
    font-size: 12px;
    color: #dc2626;
    margin-top: 6px;
}

.input-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #f9fafb;
    border-radius: 10px;
}

.checkbox-input {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checkbox-label {
    font-size: 14px;
    color: #374151;
    margin: 0;
    cursor: pointer;
}

.discount-preview {
    background: linear-gradient(135deg, #A20B0B 0%, #770C0C 100%);
    color: white;
    padding: 24px;
    border-radius: 12px;
    text-align: center;
    margin-top: 16px;
}

.discount-preview-value {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 8px;
}

.discount-preview-type {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
}

.form-actions {
    display: flex;
    gap: 12px;
    padding-top: 24px;
    border-top: 2px solid #f3f4f6;
}

.btn {
    padding: 14px 28px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #A20B0B;
    color: white;
    flex: 1;
}

.btn-primary:hover {
    background: #770C0C;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

@media (max-width: 768px) {
    .voucher-edit-container {
        padding: 16px 12px;
    }
    
    .card-header {
        padding: 24px 20px;
    }
    
    .card-header h1 {
        font-size: 22px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .input-group {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-primary {
        width: 100%;
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
            Edit Voucher
        </div>
        <div class="create-header-spacer"></div>
    </div>

<div class="voucher-edit-container">

    <div class="edit-card">
        <!-- Header -->
        <div class="card-header">
            <h1>
                <i class="fa fa-edit"></i> Edit Voucher
            </h1>
            <p>Perbarui informasi voucher Anda</p>
        </div>

        <!-- Body -->
        <div class="card-body">
            <form action="{{ route('seller.vouchers.update', $voucher->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa fa-info-circle"></i> Informasi Dasar
                    </h3>

                    <div class="form-group">
                        <label class="form-label">
                            Nama Voucher <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            class="form-input @error('name') is-invalid @enderror"
                            value="{{ old('name', $voucher->name) }}"
                            placeholder="Contoh: Diskon Akhir Tahun"
                            required
                        >
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Kode Voucher <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="code" 
                            class="form-input @error('code') is-invalid @enderror"
                            value="{{ old('code', $voucher->code) }}"
                            placeholder="Contoh: NEWYEAR2024"
                            style="text-transform: uppercase;"
                            required
                        >
                        <span class="form-help">Kode harus unik dan huruf kapital</span>
                        @error('code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea 
                            name="description" 
                            class="form-textarea @error('description') is-invalid @enderror"
                            placeholder="Jelaskan ketentuan dan manfaat voucher..."
                        >{{ old('description', $voucher->description) }}</textarea>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Discount Settings -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa fa-tags"></i> Pengaturan Diskon
                    </h3>

                    <div class="input-group">
                        <div class="form-group">
                            <label class="form-label">
                                Tipe Diskon <span class="required">*</span>
                            </label>
                            <select 
                                name="discount_type" 
                                id="discountType"
                                class="form-select @error('discount_type') is-invalid @enderror"
                                required
                            >
                                <option value="percentage" {{ old('discount_type', $voucher->discount_type) === 'percentage' ? 'selected' : '' }}>
                                    Persentase (%)
                                </option>
                                <option value="fixed" {{ old('discount_type', $voucher->discount_type) === 'fixed' ? 'selected' : '' }}>
                                    Nominal (Rp)
                                </option>
                            </select>
                            @error('discount_type')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Nilai Diskon <span class="required">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="discount_value" 
                                id="discountValue"
                                class="form-input @error('discount_value') is-invalid @enderror"
                                value="{{ old('discount_value', $voucher->discount_value) }}"
                                min="1"
                                required
                            >
                            @error('discount_value')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group" id="maxDiscountGroup" style="{{ old('discount_type', $voucher->discount_type) === 'percentage' ? '' : 'display: none;' }}">
                        <label class="form-label">Maksimal Diskon (Rp)</label>
                        <input 
                            type="number" 
                            name="max_discount" 
                            class="form-input @error('max_discount') is-invalid @enderror"
                            value="{{ old('max_discount', $voucher->max_discount) }}"
                            placeholder="Kosongkan jika tidak ada batas"
                        >
                        <span class="form-help">Batas maksimal potongan untuk diskon persentase</span>
                        @error('max_discount')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Discount Preview -->
                    <div class="discount-preview">
                        <div class="discount-preview-value" id="previewValue">
                            {{ $voucher->formatted_discount }}
                        </div>
                        <div class="discount-preview-type" id="previewType">
                            {{ $voucher->discount_type === 'percentage' ? 'Diskon Persentase' : 'Diskon Nominal' }}
                        </div>
                    </div>
                </div>

                <!-- Requirements -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa fa-cog"></i> Ketentuan Voucher
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Minimal Transaksi (Rp)</label>
                        <input 
                            type="number" 
                            name="min_transaction" 
                            class="form-input @error('min_transaction') is-invalid @enderror"
                            value="{{ old('min_transaction', $voucher->min_transaction) }}"
                            min="0"
                            placeholder="0"
                        >
                        <span class="form-help">Kosongkan atau 0 jika tidak ada minimal transaksi</span>
                        @error('min_transaction')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Validity Period -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa fa-calendar"></i> Periode Berlaku
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Mulai Berlaku</label>
                        <input 
                            type="datetime-local" 
                            name="valid_from" 
                            class="form-input @error('valid_from') is-invalid @enderror"
                            value="{{ old('valid_from', $voucher->valid_from ? $voucher->valid_from->format('Y-m-d\TH:i') : '') }}"
                        >
                        @error('valid_from')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Berakhir Pada</label>
                        <input 
                            type="datetime-local" 
                            name="valid_until" 
                            class="form-input @error('valid_until') is-invalid @enderror"
                            value="{{ old('valid_until', $voucher->valid_until ? $voucher->valid_until->format('Y-m-d\TH:i') : '') }}"
                        >
                        @error('valid_until')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa fa-toggle-on"></i> Status
                    </h3>

                    <div class="checkbox-group">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            id="isActive"
                            class="checkbox-input"
                            value="1"
                            {{ old('is_active', $voucher->is_active) ? 'checked' : '' }}
                        >
                        <label for="isActive" class="checkbox-label">
                            <strong>Aktifkan voucher ini</strong> - Voucher dapat diklaim dan digunakan oleh pelanggan
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('seller.vouchers.show', $voucher->id) }}" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountType = document.getElementById('discountType');
    const discountValue = document.getElementById('discountValue');
    const maxDiscountGroup = document.getElementById('maxDiscountGroup');
    const previewValue = document.getElementById('previewValue');
    const previewType = document.getElementById('previewType');

    function updatePreview() {
        const type = discountType.value;
        const value = discountValue.value || 0;

        if (type === 'percentage') {
            previewValue.textContent = value + '%';
            previewType.textContent = 'Diskon Persentase';
            maxDiscountGroup.style.display = '';
        } else {
            previewValue.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            previewType.textContent = 'Diskon Nominal';
            maxDiscountGroup.style.display = 'none';
        }
    }

    discountType.addEventListener('change', updatePreview);
    discountValue.addEventListener('input', updatePreview);

    // Initial update
    updatePreview();
});
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 2000
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session("error") }}',
        confirmButtonColor: '#A20B0B'
    });
</script>
@endif
@endsection