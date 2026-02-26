<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 antialiased">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="/" class="flex justify-center text-2xl font-bold text-gray-900 mb-6">
            {{ config('app.name') }}
        </a>
        <h2 class="text-center text-2xl font-semibold text-gray-800">
            @yield('heading')
        </h2>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm rounded-xl border border-gray-200">
            @yield('content')
        </div>
    </div>

    @livewireScripts
</body>
</html>
