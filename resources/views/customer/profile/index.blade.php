@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
<style>
/* Back Button */
.back-button {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #fff;
    border-bottom: 1px solid #eee;
    padding: 12px 16px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #333;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-back:hover {
    color: #ff6a2a;
}

.btn-back i {
    font-size: 18px;
}

.profile-actions {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* base */
.btn-action {
    width: 100%;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 500;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

/* primary */
.btn-primary {
    background: #ff6a2a;
    color: #fff;
    border: none;
}

.btn-primary:hover {
    background: #e85b1f;
}

/* secondary */
.btn-secondary {
    border: 1.5px solid #ddd;
    color: #333;
}

.btn-secondary:hover {
    background: #f5f5f5;
}

/* voucher style */
.btn-voucher {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.btn-voucher:hover {
    background: linear-gradient(135deg, #5568d3 0%, #66408a 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* danger */
.btn-danger {
    border: 2px solid #ff4d4f;
    color: #ff4d4f;
}

.btn-danger:hover {
    background: rgba(255, 77, 79, 0.08);
}
</style>

@section('content')
<div class="container-fluid p-0">

    <!-- BACK BUTTON -->
    <div class="back-button">
        <a href="{{ route('home') }}" class="btn-back">
            <i class="fa fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- PROFILE HEADER -->
    <div class="bg-white">
        <div class="text-center py-5">
            <div class="position-relative d-inline-block mb-3">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}"
                         class="rounded-circle"
                         style="width:100px;height:100px;object-fit:cover;border:3px solid #ff5722;">
                @else
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                         style="width:100px;height:100px;border:3px solid #ff5722;">
                        <i class="fa fa-user text-white" style="font-size:40px;"></i>
                    </div>
                @endif
            </div>

            <h5 class="fw-bold mb-2">{{ $user->name }}</h5>
            <p class="text-muted mb-0">
                <i class="fa fa-phone me-2"></i>{{ $user->phone }}
            </p>
        </div>
    </div>

    <!-- INFORMASI AKUN -->
    <div class="p-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Informasi Akun</h6>

                <div class="row mb-3">
                    <div class="col-4">
                        <small class="text-muted">Nama</small>
                    </div>
                    <div class="col-8">
                        <p class="mb-0">{{ $user->name }}</p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-4">
                        <small class="text-muted">No. Telepon</small>
                    </div>
                    <div class="col-8">
                        <p class="mb-0">{{ $user->phone }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- AKSI AKUN -->
<div class="p-3 pt-0">
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <h6 class="fw-bold mb-3">Aksi Akun</h6>
            
            {{-- ALAMAT PENGIRIMAN --}}
            <a href="{{ route('customer.addresses.index') }}"
               class="btn-action btn-secondary mb-3">
                <i class="fas fa-map-marker-alt"></i>
                <span>Alamat Pengiriman</span>
            </a>

            <small class="text-muted d-block mb-3">
                Kelola dan tambahkan alamat untuk pengiriman pesanan.
            </small>

            {{-- VOUCHER SAYA --}}
            <a href="{{ route('customer.vouchers.my') }}"
               class="btn-action btn-voucher mb-3">
                <i class="fas fa-ticket"></i>
                <span>Voucher Saya</span>
            </a>

            <small class="text-muted d-block mb-3">
                Lihat dan kelola voucher yang telah Anda klaim.
            </small>

            @auth

                {{-- 1️⃣ SUDAH SELLER --}}
                @if(Auth::user()->role === 'seller')
                    <form method="POST" action="{{ route('account.switch') }}">
                        @csrf
                        <button type="submit" class="btn-action btn-primary">
                            <i class="fas fa-redo"></i>
                            <span>Beralih ke Akun Seller</span>
                        </button>
                    </form>

                {{-- 2️⃣ PENGAJUAN SEDANG DIPROSES --}}
                @elseif($sellerRequest && $sellerRequest->status === 'pending')
                    <button class="btn-action btn-secondary" disabled>
                        <i class="fas fa-clock"></i>
                        <span>Pengajuan Sedang Ditinjau</span>
                    </button>

                    <small class="text-muted d-block mt-2">
                        Pengajuan kamu sedang ditinjau admin.
                    </small>

                {{-- 3️⃣ PENGAJUAN DITOLAK --}}
                @elseif($sellerRequest && $sellerRequest->status === 'rejected')
                    <a href="{{ route('seller-request.create') }}"
                       class="btn-action btn-primary">
                        <i class="fas fa-rotate-right"></i>
                        <span>Ajukan Ulang Jadi Seller</span>
                    </a>

                    @if($sellerRequest->admin_notes)
                        <small class="text-danger d-block mt-2">
                            Alasan penolakan: {{ $sellerRequest->admin_notes }}
                        </small>
                    @endif

                {{-- 4️⃣ BELUM PERNAH AJUKAN --}}
                @elseif(Auth::user()->user_verified_at)
                    <a href="{{ route('seller-request.create') }}"
                       class="btn-action btn-primary">
                        <i class="fas fa-store"></i>
                        <span>Ajukan Jadi Seller</span>
                    </a>

                {{-- 5️⃣ BELUM VERIFIKASI --}}
                @else
                    <a href="{{ route('seller-request.create') }}"
                       class="btn-action btn-secondary"
                       onclick="needVerify(event)">
                        <i class="fas fa-user-check"></i>
                        <span>Verifikasi Akun untuk Jadi Seller</span>
                    </a>

                    <small class="text-muted d-block mt-2">
                        Akun harus diverifikasi sebelum mengajukan seller.
                    </small>
                @endif

            @endauth

        </div>
    </div>
</div>

    <!-- ACTION BUTTONS -->
    <div class="profile-actions">

        <a href="{{ route('profile.edit') }}" class="btn-action btn-secondary">
            <i class="fa fa-edit"></i>
            <span>Edit Profil</span>
        </a>

        <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="btn-action btn-danger"
                onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="fa fa-sign-out-alt"></i>
                <span>Keluar</span>
            </button>
        </form>

    </div>

</div>
@endsection