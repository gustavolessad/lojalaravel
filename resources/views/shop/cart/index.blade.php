@extends('layouts.shop')

@section('title', 'Carrinho de Compras')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Carrinho de Compras</h1>
    </div>

    @livewire('shop.cart-page')
@endsection
