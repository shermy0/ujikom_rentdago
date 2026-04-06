@extends('kurir.layouts.master')

@section('navbar')
<div class="mobile-top-header">
    <div class="header-left">
        <a href="{{ route('kurir.orders') }}" class="text-dark">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
    <div class="header-center">
        <h5 class="mb-0 fw-bold">Konfirmasi Penyerahan</h5>
    </div>
</div>
@endsection

@section('navbot')
@include('kurir.layouts.navbot')
@endsection

@section('content')
<div class="container pb-5">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="fa fa-map-marker-alt text-success"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Sudah Sampai di Customer</h6>
                    <small class="text-muted">Pilih metode verifikasi penyerahan barang</small>
                </div>
            </div>

            <div class="bg-light rounded-3 p-3">
                <div class="row g-2 small">
                    <div class="col-4 text-muted">Kode Order</div>
                    <div class="col-8 fw-bold">#{{ $shipment->order->order_code }}</div>

                    <div class="col-4 text-muted">Customer</div>
                    <div class="col-8">{{ $shipment->order->user->name }}</div>
                </div>
            </div>
        </div>
    </div>


        <!-- Photo Method -->
        <div class="col-12">
            <a href="{{ route('kurir.delivery-photo.show', $shipment->id) }}" class="card border-0 shadow-sm w-100 text-start p-3 text-decoration-none hover-effect">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fa fa-camera text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Bukti Foto Handover</h6>
                        <small class="text-muted">Ambil foto produk bersama Customer</small>
                    </div>
                    <i class="fa fa-chevron-right ms-auto text-muted"></i>
                </div>
            </a>
        </div>
    </div>
</div>



<style>
    .hover-effect:active {
        background-color: #f8f9fa;
        transform: scale(0.98);
        transition: transform 0.1s;
    }
</style>
@endsection

@push('scripts')
        <div class="row g-3">