<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title', 'Acesso')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <span class="text-5xl">🖥</span>
            <h1 class="mt-3 text-3xl font-bold text-brand-700">HelpDesk TI</h1>
            <p class="mt-1 text-sm text-gray-500">Sistema de Chamados</p>
        </div>
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-md rounded-xl border border-gray-100">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </div>
</div>
@livewireScripts
</body>
</html>
