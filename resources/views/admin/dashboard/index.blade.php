@extends('admin.layouts.app')

@section('content')
<style>
    .admin-dashboard {
    padding: 20px;
    background: #fff;
}

.dashboard-title {
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

/* Cards */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    border-left: 5px solid #EE4D2D;
    box-shadow: 0 4px 12px rgba(0,0,0,.05);
}

.stat-card p {
    font-size: 14px;
    color: #777;
}

.stat-card h2 {
    margin: 0;
    color: #EE4D2D;
}

/* Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.dashboard-box {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,.05);
}

.dashboard-box h4 {
    margin-bottom: 15px;
    color: #EE4D2D;
}

/* List */
.list-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f1f1f1;
    font-size: 14px;
}

.empty-text {
    font-size: 14px;
    color: #aaa;
}

.stat-head{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:8px;
}

.stat-head i{
    font-size:20px;
    color:#EE4D2D;
}

.list-user{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 0;
    border-bottom:1px solid #f1f1f1;
}

.user-left{
    display:flex;
    align-items:center;
    gap:10px;
}

.avatar{
    width:36px;
    height:36px;
    border-radius:50%;
    background:#EE4D2D;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:600;
}

.muted{
    font-size:12px;
    color:#888;
}
.order-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 0;
    border-bottom:1px solid #f1f1f1;
}

.order-left{
    display:flex;
    gap:10px;
    align-items:center;
}

.order-img{
    width:45px;
    height:45px;
    border-radius:8px;
    object-fit:cover;
    background:#f3f3f3;
}

.status-pill{
    font-size:11px;
    padding:3px 8px;
    border-radius:20px;
    background:#eee;
}

.status-pill.completed{ background:#d1fae5; color:#065f46; }
.status-pill.pending{ background:#fee2e2; color:#991b1b; }
.status-pill.confirmed{ background:#dbeafe; color:#1e40af; }

</style>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="admin-dashboard">

    <h3 class="dashboard-title">Dashboard</h3>

    {{-- STAT CARDS --}}
{{-- STAT CARDS --}}
<div class="dashboard-cards">

    <div class="stat-card">
        <div class="stat-head">
            <i class="fas fa-users"></i>
            <p>Total User</p>
        </div>
        <h2>{{ $totalUsers }}</h2>
    </div>

    <div class="stat-card">
        <div class="stat-head">
            <i class="fas fa-store"></i>
            <p>Total Seller</p>
        </div>
        <h2>{{ $totalSellers }}</h2>
    </div>

    <div class="stat-card">
        <div class="stat-head">
            <i class="fas fa-box"></i>
            <p>Total Produk</p>
        </div>
        <h2>{{ $totalProducts }}</h2>
    </div>

    <div class="stat-card">
        <div class="stat-head">
            <i class="fas fa-shopping-cart"></i>
            <p>Total Pesanan</p>
        </div>
        <h2>{{ $totalOrders }}</h2>
    </div>

</div>


    </div>

    {{-- CONTENT GRID --}}
    <div class="dashboard-grid">

        {{-- REQUEST SELLER --}}
        <div class="dashboard-box">
            <h4>Request Seller Terbaru</h4>

@forelse($latestSellerRequests as $req)
<div class="list-user">

    <div class="user-left">
        <div class="avatar">
            {{ strtoupper(substr($req->user->name,0,1)) }}
        </div>

        <div>
            <strong>{{ $req->user->name }}</strong>
            <div class="muted">{{ $req->user->phone }}</div>
        </div>
    </div>

    <small>{{ $req->created_at->locale('id')->diffForHumans() }}</small>
</div>
@empty
<p class="empty-text">Belum ada request</p>
@endforelse

        </div>

        {{-- PESANAN TERBARU --}}
        <div class="dashboard-box">
            <h4>Pesanan Terbaru</h4>

@forelse($latestOrders as $order)
<div class="order-item">

    <div class="order-left">
        <img 
            src="{{ asset('storage/'.$order->productRental->product->images->first()->image_path ?? 'placeholder.png') }}"
            class="order-img"
        >

        <div>
            <strong>{{ $order->productRental->product->name }}</strong>
            <div class="muted">
                {{ $order->user->name }} • #{{ $order->id }}
            </div>
        </div>
    </div>
    @php
        $statusLabels = [
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'ongoing' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
    @endphp
    <span class="status-pill {{ $order->status }}">
       {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
    </span>

    <small>{{ $order->created_at->locale('id')->diffForHumans() }}</small>

</div>
@empty
<p class="empty-text">Belum ada pesanan</p>
@endforelse


    </div>
</div>
@endsection
