@extends('frontend.masterseller')

@section('content')
<style>
* {
    box-sizing: border-box;
}

.voucher-detail-container {
    padding: 24px 16px;
    max-width: 1200px;
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
    color: #3b82f6;
}

.detail-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 32px;
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.voucher-title-section {
    flex: 1;
}

.voucher-title {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 8px;
}

.voucher-code-display {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 10px 20px;
    border-radius: 10px;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 1px;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.btn-white {
    background: white;
    color: #667eea;
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-white:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.discount-showcase {
    text-align: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
}

.discount-value {
    font-size: 56px;
    font-weight: 700;
    margin-bottom: 8px;
}

.discount-type-label {
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
}

.card-body {
    padding: 32px;
}

.info-section {
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
    color: #3b82f6;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-item {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
}

.info-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.info-value {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.description-box {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
}

.description-text {
    font-size: 15px;
    color: #374151;
    line-height: 1.6;
}

.status-badge-large {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.badge-active {
    background: #dcfce7;
    color: #16a34a;
}

.badge-inactive {
    background: #fee2e2;
    color: #dc2626;
}

.badge-expired {
    background: #f3f4f6;
    color: #6b7280;
}

.stats-section {
    background: #f9fafb;
    padding: 24px;
    border-radius: 12px;
    margin-top: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 16px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #3b82f6;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 13px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.empty-description {
    color: #9ca3af;
    font-style: italic;
}

@media (max-width: 768px) {
    .voucher-detail-container {
        padding: 16px 12px;
    }
    
    .card-header {
        padding: 24px 20px;
    }
    
    .voucher-title {
        font-size: 24px;
    }
    
    .discount-value {
        font-size: 40px;
    }
    
    .header-top {
        flex-direction: column;
    }
    
    .header-actions {
        width: 100%;
    }
    
    .btn-white {
        flex: 1;
        justify-content: center;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.vouchers.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Detail Voucher
        </div>
        <div class="create-header-spacer"></div>
    </div>

<div class="voucher-detail-container">

    <div class="detail-card">
        <!-- Header -->
        <div class="card-header">
            <div class="header-top">
                <div class="voucher-title-section">
                    <h1 class="voucher-title">{{ $voucher->name }}</h1>
                    <div class="voucher-code-display">{{ $voucher->code }}</div>
                </div>
                
                <div class="header-actions">
                    <a href="{{ route('seller.vouchers.edit', $voucher->id) }}" class="btn-white">
                        <i class="fa fa-edit"></i> Edit Voucher
                    </a>
                </div>
            </div>

            <div class="discount-showcase">
                <div class="discount-value">{{ $voucher->formatted_discount }}</div>
                <div class="discount-type-label">
                    {{ $voucher->discount_type === 'percentage' ? 'Diskon Persentase' : 'Diskon Nominal' }}
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body">
            <!-- Status -->
            <div class="info-section">
                <h3 class="section-title">
                    <i class="fa fa-info-circle"></i> Status Voucher
                </h3>
                <span class="status-badge-large badge-{{ strtolower($voucher->status_label) === 'aktif' ? 'active' : (strtolower($voucher->status_label) === 'nonaktif' ? 'inactive' : 'expired') }}">
                    {{ $voucher->status_label }}
                </span>
            </div>

            <!-- Description -->
            @if($voucher->description)
            <div class="info-section">
                <h3 class="section-title">
                    <i class="fa fa-file-text"></i> Deskripsi
                </h3>
                <div class="description-box">
                    <p class="description-text">{{ $voucher->description }}</p>
                </div>
            </div>
            @endif

            <!-- Voucher Details -->
            <div class="info-section">
                <h3 class="section-title">
                    <i class="fa fa-cog"></i> Detail Voucher
                </h3>
                <div class="info-grid">
                    @if($voucher->discount_type === 'percentage' && $voucher->max_discount)
                    <div class="info-item">
                        <div class="info-label">Maksimal Diskon</div>
                        <div class="info-value">Rp {{ number_format($voucher->max_discount, 0, ',', '.') }}</div>
                    </div>
                    @endif

                    @if($voucher->min_transaction > 0)
                    <div class="info-item">
                        <div class="info-label">Minimal Transaksi</div>
                        <div class="info-value">Rp {{ number_format($voucher->min_transaction, 0, ',', '.') }}</div>
                    </div>
                    @endif

                    @if($voucher->valid_from)
                    <div class="info-item">
                        <div class="info-label">Berlaku Dari</div>
                        <div class="info-value">{{ $voucher->valid_from->format('d M Y H:i') }}</div>
                    </div>
                    @endif

                    @if($voucher->valid_until)
                    <div class="info-item">
                        <div class="info-label">Berlaku Hingga</div>
                        <div class="info-value">{{ $voucher->valid_until->format('d M Y H:i') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-section">
                <h3 class="section-title">
                    <i class="fa fa-chart-bar"></i> Statistik Penggunaan
                </h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">{{ $voucher->claimed_count ?? 0 }}</div>
                        <div class="stat-label">Diklaim User</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection