{{-- resources/views/customer/chat/index.blade.php --}}

@extends('frontend.master')
@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/chat-shop.css') }}">

<div class="chat-container">
    @livewire('customer-chat-list')
</div>

@endsection