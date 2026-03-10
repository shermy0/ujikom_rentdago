@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-shop me-2"></i>Detail Toko
        </h5>
        <div>
            <a href="{{ route('admin.shops.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                @if($shop->logo)
                    <img src="{{ asset('storage/' . $shop->logo) }}" 
                         alt="{{ $shop->name_store }}" 
                         class="shop-detail-logo rounded mb-3">
                @else
                    <div class="shop-detail-logo-placeholder rounded mb-3">
                        <i class="bi bi-shop"></i>
                    </div>
                @endif
                
                <h4 class="mb-1">{{ $shop->name_store }}</h4>
                <p class="text-muted mb-2">{{ $shop->slug }}</p>
                
                @if($shop->is_active)
                    <span class="badge bg-success fs-6">Aktif</span>
                @else
                    <span class="badge bg-secondary fs-6">Nonaktif</span>
                @endif
            </div>
            
            <div class="col-md-8">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID Toko</th>
                        <td>{{ $shop->id }}</td>
                    </tr>
                    <tr>
                        <th>Pemilik</th>
                        <td>
                            @if($shop->user)
                                <span class="badge bg-info">{{ $shop->user->name }}</span>
                                <br><small class="text-muted">{{ $shop->user->phone }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>{{ $shop->description ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $shop->address_store }}</td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>{{ $shop->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Diupdate</th>
                        <td>{{ $shop->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        border-radius: 12px;
    }
    .card-header {
        background: white;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px 25px;
        border-radius: 12px 12px 0 0 !important;
    }
    .card-body {
        padding: 25px;
    }
    .btn-primary {
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #d94429, #e55a2b);
    }
    .shop-detail-logo {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border: 3px solid #f0f0f0;
    }
    .shop-detail-logo-placeholder {
        width: 180px;
        height: 180px;
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 64px;
    }
    .table th {
        color: #666;
        font-weight: 600;
    }
</style>
@endpush
@endsection
