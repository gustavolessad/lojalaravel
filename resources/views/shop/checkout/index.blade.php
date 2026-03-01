@extends('layouts.minimal')

@section('title', 'Checkout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @livewire('shop.checkout-page')
</div>
@endsection
