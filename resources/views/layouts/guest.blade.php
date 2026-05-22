<!DOCTYPE html>
<html lang="pt-BR" data-theme="helpdesk"
      x-data x-init="const t = localStorage.getItem('app_theme'); if (t) $el.setAttribute('data-theme', t);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title', 'Acesso')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-base-200 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="text-center mb-8">
        
        <h1 class="mt-3 text-3xl font-bold text-primary">HelpDesk</h1>
        <p class="mt-1 text-sm text-base-content/60">Sistema de fluxo de trabalho</p>
    </div>

    <div class="card bg-base-100 shadow-xl w-full max-w-md">
        <div class="card-body">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </div>

@livewireScripts
</body>
</html>
