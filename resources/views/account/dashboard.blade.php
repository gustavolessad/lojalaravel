@extends('layouts.account')

@section('title', 'Minha Conta')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            Olá, {{ $customer->display_name }}!
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Bem-vindo(a) ao seu painel.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="#" class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-400 transition-colors">
            <div class="text-2xl mb-2">📦</div>
            <h3 class="font-medium text-gray-900">Meus pedidos</h3>
            <p class="text-sm text-gray-500 mt-1">Acompanhe seus pedidos</p>
        </a>

        <a href="{{ route('account.addresses.index') }}" class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-400 transition-colors">
            <div class="text-2xl mb-2">📍</div>
            <h3 class="font-medium text-gray-900">Endereços</h3>
            <p class="text-sm text-gray-500 mt-1">Gerencie seus endereços de entrega</p>
        </a>

        <a href="#" class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-400 transition-colors">
            <div class="text-2xl mb-2">👤</div>
            <h3 class="font-medium text-gray-900">Meus dados</h3>
            <p class="text-sm text-gray-500 mt-1">Edite seu cadastro e senha</p>
        </a>
    </div>
@endsection
