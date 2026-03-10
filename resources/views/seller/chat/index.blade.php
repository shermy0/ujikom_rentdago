{{-- resources/views/seller/chat/index.blade.php --}}

@extends('frontend.masterseller')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/chat-shop.css') }}?v={{ time() }}">

<div class="chat-container">
    @livewire('seller-chat-list')
</div>

@endsection