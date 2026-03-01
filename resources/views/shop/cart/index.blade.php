@extends('layouts.minimal')

@section('title', 'Carrinho de Compras')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Carrinho de Compras</h1>
    </div>

    @livewire('shop.cart-page')
</div>
@endsection
