{{-- resources/views/seller/chat/show.blade.php --}}

@extends('frontend.masterseller')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/chat-shop.css') }}">

@livewire('seller-chat-room', ['customerId' => $customerId])

@endsection