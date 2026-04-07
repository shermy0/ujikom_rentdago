@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/address-customer.css') }}?v={{ time() }}">



    {{-- HEADER DETAIL PRODUK --}}
    <div class="product-detail-header">
        <a href="{{ url()->previous() }}"" class="header-back">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="header-title">Alamat Saya</div>
        <div class="header-spacer"></div>
    </div>
<div class="container py-4" style="max-width: 720px;">


    {{-- ACTION TOP --}}
<div class="address-top-action">
    <a href="{{ route('customer.addresses.create') }}" class="btn-add-address">
        <i class="fa fa-plus"></i>
        Tambah Alamat
    </a>
</div>

{{-- LIST ALAMAT --}}
@forelse ($addresses as $address)
    <div class="address-card {{ $address->is_default ? 'is-default' : '' }}">
        <div class="address-card-body">

            <div class="address-main">
                <div class="address-badges">
                    <span class="badge badge-label">
                        {{ $address->label }}
                    </span>

                    @if ($address->is_default)
                        <span class="badge badge-default">
                            Alamat Utama
                        </span>
                    @endif
                </div>

                <div class="address-receiver">
                    {{ $address->receiver_name }}
                    <span>• {{ $address->receiver_phone }}</span>
                </div>

                <div class="address-text">
                    {{ $address->address }}
                </div>

                @if ($address->notes)
                    <div class="address-notes">
                        Catatan: {{ $address->notes }}
                    </div>
                @endif
            </div>

<div class="address-action">

    <a href="{{ route('customer.addresses.edit', $address) }}"
       class="btn-action btn-edit">
        Edit
    </a>

<form action="{{ route('customer.addresses.destroy', $address) }}"
      method="POST"
      class="delete-address-form">
    @csrf
    @method('DELETE')

    <button class="btn-action btn-delete" type="submit">
        Hapus
    </button>
</form>

        @if (! $address->is_default)
        <form action="{{ route('customer.addresses.set-default', $address) }}"
              method="POST">
            @csrf
            <button class="btn-action btn-default" type="submit">
                Jadikan Utama
            </button>
        </form>
    @endif

</div>


        </div>
    </div>
@empty
    <div class="text-center text-muted py-5">
        <p>Belum ada alamat</p>
    </div>
@endforelse
</div>
<script>
document.querySelectorAll('.delete-address-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Hapus Alamat?',
            text: 'Alamat ini akan dihapus dari daftar Anda.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@if(session('error'))
<script>
Swal.fire({
    icon: 'warning',
    title: 'Tidak Bisa Dihapus',
    text: '{{ session('error') }}'
});
</script>
@endif

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '{{ session('success') }}'
});
</script>
@endif

@endsection
