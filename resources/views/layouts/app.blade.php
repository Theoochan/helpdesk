<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title', 'HelpDesk')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">

<div class="min-h-full">
    <nav class="bg-brand-700 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="text-white font-bold text-xl tracking-tight">
                        🖥 HelpDesk
                    </a>

                    @auth
                    @php
                        $navUnreadTickets = \App\Models\TicketRead::unreadCountFor(auth()->user());
                        $navUnreadOrders  = auth()->user()->isTechnician()
                            ? \App\Models\ServiceOrderRead::unreadCountFor(auth()->user())
                            : 0;
                    @endphp
                    <div class="hidden md:flex gap-1 ml-6">
                        @if(auth()->user()->isTechnician())
                            <a href="{{ route('technician.dashboard') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-brand-100 hover:bg-brand-600 hover:text-white transition-colors">
                                Dashboard
                            </a>
                            <a href="{{ route('tickets.index') }}"
                               class="relative px-3 py-2 rounded-md text-sm font-medium text-brand-100 hover:bg-brand-600 hover:text-white transition-colors">
                                Chamados
                                @if($navUnreadTickets > 0)
                                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-[18px] text-center bg-red-500 text-white rounded-full">
                                        {{ $navUnreadTickets > 99 ? '99+' : $navUnreadTickets }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('orders.index') }}"
                               class="relative px-3 py-2 rounded-md text-sm font-medium text-brand-100 hover:bg-brand-600 hover:text-white transition-colors">
                                OS Internas
                                @if($navUnreadOrders > 0)
                                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-[18px] text-center bg-red-500 text-white rounded-full">
                                        {{ $navUnreadOrders > 99 ? '99+' : $navUnreadOrders }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('reports.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-brand-100 hover:bg-brand-600 hover:text-white transition-colors">
                                Relatórios
                            </a>
                            @can('manage-technicians')
                            <a href="{{ route('admin.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-purple-200 hover:bg-purple-600 hover:text-white transition-colors">
                                ⚙ Administração
                            </a>
                            @endcan
                        @else
                            <a href="{{ route('tickets.index') }}"
                               class="relative px-3 py-2 rounded-md text-sm font-medium text-brand-100 hover:bg-brand-600 hover:text-white transition-colors">
                                Meus Chamados
                                @if($navUnreadTickets > 0)
                                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-[18px] text-center bg-red-500 text-white rounded-full">
                                        {{ $navUnreadTickets > 99 ? '99+' : $navUnreadTickets }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('tickets.create') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-brand-100 hover:bg-brand-600 hover:text-white transition-colors">
                                Abrir Chamado
                            </a>
                        @endif
                    </div>
                    @endauth
                </div>

                @auth
                <div class="flex items-center gap-3">
                    <span class="text-brand-200 text-sm hidden sm:block">
                        {{ auth()->user()->name }}
                        @php
                            $roleLabel = match(auth()->user()->role) {
                                'admin'        => 'Admin',
                                'technician'   => 'Técnico',
                                default        => 'Colaborador',
                            };
                            $roleBg = match(auth()->user()->role) {
                                'admin'      => 'bg-purple-500',
                                'technician' => 'bg-brand-500',
                                default      => 'bg-gray-500',
                            };
                        @endphp
                        <span class="ml-1 text-xs {{ $roleBg }} text-white px-1.5 py-0.5 rounded-full">
                            {{ $roleLabel }}
                        </span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-brand-200 hover:text-white text-sm px-3 py-2 rounded-md hover:bg-brand-600 transition-colors">
                            Sair
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-4 right-4 z-50 bg-green-500 text-white px-5 py-3 rounded-lg shadow-lg text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-4 right-4 z-50 bg-red-500 text-white px-5 py-3 rounded-lg shadow-lg text-sm">
        {{ session('error') }}
    </div>
    @endif

    @hasSection('header')
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
            @yield('header')
        </div>
    </header>
    @endif

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>
