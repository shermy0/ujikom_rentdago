@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/order.css') }}">

<div class="order-container">

    <div class="order-header">
        <a href="{{ route('customer.order.show', $penalty->order_id) }}" class="header-back">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="header-title">Bayar Denda</div>
    </div>

    <div class="order-card">
        <h4>Bayar Denda</h4>

        <p style="margin-top:10px">
            Denda keterlambatan pengembalian barang
        </p>

        <h3 style="color:#dc3545">
            Rp {{ number_format($penalty->penalties_amount, 0, ',', '.') }}
        </h3>

        <button id="payPenalty" class="pay-button">
            Bayar Sekarang
        </button>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('payPenalty').addEventListener('click', function () {
    snap.pay('{{ $snapToken }}', {
        onSuccess: function (result) {

            fetch("{{ url('/dev/midtrans/penalty-callback') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    midtrans_order_id: "{{ $penalty->midtrans_order_id }}",
                    transaction_status: "settlement"
                })
            })
            .then(res => res.json())
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil',
                    text: 'Denda keterlambatan berhasil dibayar.',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    window.location.href =
                        "{{ route('customer.order.show', $penalty->order_id) }}";
                });
            });
        },

        onPending: function () {
            Swal.fire({
                icon: 'info',
                title: 'Menunggu Pembayaran',
                text: 'Silakan selesaikan pembayaran Anda.',
            });
        },

        onError: function () {
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Gagal',
                text: 'Terjadi kesalahan saat pembayaran.',
            });
        }
    });
});
</script>

@endsection
