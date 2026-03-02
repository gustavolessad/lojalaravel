@extends('layouts.minimal')

@section('body-class', 'bg-gray-100')

@section('main-class', 'flex-1 flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8')

@section('content')

<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

        {{-- Card header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                @yield('icon')
            </div>
            <h1 class="text-sm font-semibold text-gray-900">@yield('heading')</h1>
        </div>

        {{-- Form body --}}
        <div class="p-5">
            @yield('form')
        </div>

    </div>
</div>

@endsection
