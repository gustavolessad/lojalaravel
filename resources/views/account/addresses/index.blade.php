@extends('layouts.account')

@section('title', 'Meus Endereços')

@section('page-content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-medium text-gray-900">Meus endereços</h1>
        <p class="text-sm text-gray-500 mt-1">Gerencie seus endereços de entrega</p>
    </div>
    <a
        href="{{ route('account.addresses.create') }}"
        class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
        + Novo endereço
    </a>
</div>

@if ($addresses->isEmpty())
<div class="text-center py-16 bg-white rounded-xl border border-gray-200">
    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
        </svg>
    </div>
    <h3 class="font-medium text-gray-900 mb-1">Nenhum endereço cadastrado</h3>
    <p class="text-sm text-gray-500 mb-4">Adicione um endereço de entrega para facilitar suas compras.</p>
    <a
        href="{{ route('account.addresses.create') }}"
        class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
        + Adicionar endereço
    </a>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach ($addresses as $address)
    <div class="bg-white rounded-xl border {{ $address->is_default ? 'border-gray-900' : 'border-gray-200' }} p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    @if ($address->label)
                    <span class="text-sm font-semibold text-gray-900">{{ $address->label }}</span>
                    @endif
                    @if ($address->is_default)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-900 text-white">
                        Padrão
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-700">
                    {{ $address->street }}, {{ $address->number }}
                    @if ($address->complement)
                    — {{ $address->complement }}
                    @endif
                </p>
                <p class="text-sm text-gray-700">{{ $address->district }}</p>
                <p class="text-sm text-gray-700">
                    {{ $address->city }} — {{ $address->state }} &bull; CEP {{ $address->cep }}
                </p>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3 border-t border-gray-100 pt-4" x-data>
            <a
                href="{{ route('account.addresses.edit', $address) }}"
                class="text-sm text-gray-600 hover:text-gray-900 font-medium">
                Editar
            </a>

            @if (! $address->is_default)
            <form method="POST" action="{{ route('account.addresses.set-default', $address) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 font-medium">
                    Definir como padrão
                </button>
            </form>

            <form
                method="POST"
                action="{{ route('account.addresses.destroy', $address) }}"
                x-on:submit.prevent="if (confirm('Remover este endereço?')) $el.submit()">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">
                    Remover
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="mt-4">
    <a href="{{ route('account.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-900">
        ← Voltar para minha conta
    </a>
</div>
@endsection