@extends('layouts.minimal')

@section('body-class', 'bg-gray-50')

@section('main-class', 'flex-1 flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8')

@section('content')

    <div class="sm:mx-auto sm:w-full sm:max-w-md mb-6">
        <h2 class="text-center text-2xl font-semibold text-gray-800">
            @yield('heading')
        </h2>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm rounded-xl border border-gray-200">
            @yield('form')
        </div>
    </div>

@endsection
