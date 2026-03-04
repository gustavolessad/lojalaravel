@extends('layouts.shop')

@section('title', 'Página não encontrada')
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Página não encontrada</h1>
        <p class="mt-2 text-gray-500">
            A página que você está procurando pode ter sido removida, renomeada ou está temporariamente indisponível.
        </p>

        <a href="/"
           class="mt-8 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-900 text-white text-sm font-semibold rounded-lg hover:bg-gray-800 transition">
            Ir para a página inicial
        </a>
    </div>
@endsection
