{{-- resources/views/customer/chat/show.blade.php --}}

@extends('frontend.master')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/chat-shop.css') }}">

@livewire('chat-room', ['shopId' => $shopId])

@endsection