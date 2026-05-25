<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title', 'HelpDesk')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-base-100 min-h-screen">

@php
    $navUnreadTickets = auth()->check()
        ? \App\Modules\Chamados\Tickets\Models\TicketRead::unreadCountFor(auth()->user())
        : 0;
    $navUnreadOrders = auth()->check() && auth()->user()->isTechnician()
        ? \App\Modules\Chamados\ServiceOrders\Models\ServiceOrderRead::unreadCountFor(auth()->user())
        : 0;
    $currentRoute = request()->route()?->getName() ?? '';
@endphp

{{-- ─── MOBILE DRAWER ───────────────────────────────────────────────────────── --}}
<div class="drawer lg:drawer-open"
     x-data="{
         open: false,
         collapsed: JSON.parse(localStorage.getItem('sidebar_collapsed') ?? 'false'),
         theme: localStorage.getItem('app_theme') ?? 'helpdesk',
         toggle() { this.collapsed = !this.collapsed; localStorage.setItem('sidebar_collapsed', this.collapsed) },
         toggleTheme() {
             this.theme = this.theme === 'helpdesk' ? 'helpdesk-dark' : 'helpdesk';
             localStorage.setItem('app_theme', this.theme);
             document.documentElement.setAttribute('data-theme', this.theme);
         },
         chamadosOpen: {{ str_starts_with($currentRoute, 'ticket') || str_starts_with($currentRoute, 'order') || $currentRoute === 'reports.index' || $currentRoute === 'technician.dashboard' ? 'true' : 'true' }}
     }"
     x-init="document.documentElement.setAttribute('data-theme', theme)">

    <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" x-model="open">

    {{-- ─── CONTEÚDO PRINCIPAL ──────────────────────────────────────────────── --}}
    <div class="drawer-content flex flex-col min-h-screen">

        {{-- TOPBAR --}}
        <header class="navbar bg-base-100/70 backdrop-blur-md border-base-300/20  border-b sticky top-0 z-30 gap-2 px-4">
            {{-- Toggle mobile --}}
            <label for="sidebar-drawer" class="btn btn-ghost btn-sm lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </label>

            {{-- Toggle desktop collapse --}}
            <button class="btn btn-ghost btn-sm hidden lg:flex" @click="toggle()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            

            <div class="flex-1"></div>

            @auth
            {{-- Badge de role + nome --}}
            <div class="hidden sm:flex items-center gap-2">
                @php
                    $roleLabel = match(auth()->user()->role) {
                        'admin'      => 'Admin',
                        'technician' => 'Técnico',
                        default      => 'Colaborador',
                    };
                    $roleColor = match(auth()->user()->role) {
                        'admin'      => 'badge-secondary',
                        'technician' => 'badge-primary',
                        default      => 'badge-neutral',
                    };
                @endphp
                <span class="text-sm text-base-content/70">{{ auth()->user()->name }}</span>
                <span class="badge {{ $roleColor }} badge-sm">{{ $roleLabel }}</span>
            </div>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                    </svg>
                    <span class="hidden sm:inline">Sair</span>
                </button>
            </form>
            @endauth
        </header>

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="fixed top-4 right-4 z-50 alert alert-success shadow-lg max-w-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="fixed top-4 right-4 z-50 alert alert-error shadow-lg max-w-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- CONTEÚDO --}}
        <main class="flex-1 p-4 sm:p-6">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    {{-- ─── SIDEBAR ─────────────────────────────────────────────────────────── --}}
    <div class="drawer-side z-40">
        <label for="sidebar-drawer" class="drawer-overlay"></label>

        <aside class="bg-base-200/70 backdrop-blur-md border-r border-base-300/60 flex flex-col min-h-screen transition-all duration-300"
               :class="collapsed ? 'w-16' : 'w-64'">

            {{-- Logo --}}
            <div class="flex items-center gap-3 px-4 py-4 border-b border-base-300 h-16">
                <span class="font-bold text-primary text-lg truncate transition-all duration-300"
                      x-show="!collapsed" x-transition>
                    HelpDesk
                </span>
                <h1 class="font-bold text-primary text-lg truncate transition-all duration-300" x-show="collapsed" x-transition>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 4v16" />
                        <path d="M18 4v16" />
                        <path d="M6 12h12" />
                    </svg>
                </h1>
            </div>

            {{-- Menu --}}
            <nav class="flex-1 overflow-y-auto py-3">
                @auth
                <ul class="menu menu-sm gap-0.5 px-2">

                    {{-- ── Módulo: Chamados ──────────────────────────────── --}}
                    @if(auth()->user()->isTechnician())

                        {{-- Dashboard --}}
                        <li>
                            <a href="{{ route('technician.dashboard') }}"
                               class="{{ $currentRoute === 'technician.dashboard' ? 'active' : '' }}"
                               title="Dashboard">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span x-show="!collapsed" x-transition class="truncate">Dashboard</span>
                            </a>
                        </li>

                        {{-- Chamados section --}}
                        <li x-data="{ open: chamadosOpen }">
                            <button @click="open = !open"
                                    class="flex items-center gap-2 w-full"
                                    :class="open ? 'active' : ''"
                                    title="Chamados">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span x-show="!collapsed" x-transition class="flex-1 text-left truncate">Chamados</span>
                                <svg x-show="!collapsed" xmlns="http://www.w3.org/2000/svg"
                                     class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <ul x-show="open && !collapsed" x-transition class="pl-2">
                                <li>
                                    <a href="{{ route('tickets.index') }}"
                                       class="{{ str_starts_with($currentRoute, 'ticket') ? 'active' : '' }}">
                                        <span class="truncate">Tickets</span>
                                        @if($navUnreadTickets > 0)
                                            <span class="badge badge-error badge-xs ml-auto">
                                                {{ $navUnreadTickets > 99 ? '99+' : $navUnreadTickets }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('orders.index') }}"
                                       class="{{ str_starts_with($currentRoute, 'order') ? 'active' : '' }}">
                                        <span class="truncate">OS Internas</span>
                                        @if($navUnreadOrders > 0)
                                            <span class="badge badge-error badge-xs ml-auto">
                                                {{ $navUnreadOrders > 99 ? '99+' : $navUnreadOrders }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('reports.index') }}"
                                       class="{{ $currentRoute === 'reports.index' ? 'active' : '' }}">
                                        <span class="truncate">Relatórios</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                    @else
                        {{-- Colaborador --}}
                        <li>
                            <a href="{{ route('tickets.index') }}"
                               class="{{ str_starts_with($currentRoute, 'ticket') ? 'active' : '' }}"
                               title="Meus Chamados">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span x-show="!collapsed" x-transition class="flex-1 truncate">Meus Chamados</span>
                                @if($navUnreadTickets > 0)
                                    <span class="badge badge-error badge-xs" x-show="!collapsed">
                                        {{ $navUnreadTickets > 99 ? '99+' : $navUnreadTickets }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tickets.create') }}"
                               class="{{ $currentRoute === 'tickets.create' ? 'active' : '' }}"
                               title="Abrir Chamado">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span x-show="!collapsed" x-transition class="truncate">Abrir Chamado</span>
                            </a>
                        </li>
                    @endif

                    {{-- ── Futuros módulos (placeholders) ───────────────── --}}
                    <li class="menu-title mt-4" x-show="!collapsed">
                        <span class="text-xs opacity-40 uppercase tracking-widest">Em breve</span>
                    </li>

                    <li>
                        <span class="opacity-40 cursor-not-allowed" title="Financeiro">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-show="!collapsed" x-transition class="truncate">Financeiro</span>
                        </span>
                    </li>
                    <li>
                        <span class="opacity-40 cursor-not-allowed" title="Jurídico">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                            <span x-show="!collapsed" x-transition class="truncate">Jurídico</span>
                        </span>
                    </li>
                    <li>
                        <span class="opacity-40 cursor-not-allowed" title="Contabilidade">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span x-show="!collapsed" x-transition class="truncate">Contabilidade</span>
                        </span>
                    </li>

                    {{-- ── Administração ─────────────────────────────────── --}}
                    @can('manage-technicians')
                    <li class="menu-title mt-4" x-show="!collapsed">
                        <span class="text-xs opacity-40 uppercase tracking-widest">Administração</span>
                    </li>
                    <div x-show="!collapsed" class="divider my-0 opacity-30"></div>
                    <li>
                        <a href="{{ route('admin.index') }}"
                           class="{{ $currentRoute === 'admin.index' ? 'active' : '' }}"
                           title="Usuários">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span x-show="!collapsed" x-transition class="truncate">Usuários</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('categories.index') }}"
                           class="{{ $currentRoute === 'categories.index' ? 'active' : '' }}"
                           title="Categorias">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2h2z"/>
                            </svg>
                            <span x-show="!collapsed" x-transition class="truncate">Categorias</span>
                        </a>
                    </li>
                    @endcan

                </ul>
                @endauth
            </nav>

            {{-- Rodapé da sidebar --}}
            <div class="p-3 border-t border-base-300 space-y-2">

                {{-- Toggle de tema --}}
                <button @click="toggleTheme()"
                        class="btn btn-ghost btn-sm w-full gap-2 justify-start"
                        :class="collapsed ? 'justify-center px-0' : ''"
                        :title="theme === 'helpdesk' ? 'Alternar para tema escuro' : 'Alternar para tema claro'">
                    <svg x-show="theme === 'helpdesk'" xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4 shrink-0 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                    <svg x-show="theme === 'helpdesk-dark'" xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                    <span x-show="!collapsed" x-transition class="text-xs truncate"
                          x-text="theme === 'helpdesk' ? 'Tema claro' : 'Tema escuro'"></span>
                </button>

                {{-- Avatar + dropdown de perfil --}}
                @auth
                <div x-data="{ profileOpen: false }" class="relative">
                    <button @click="profileOpen = !profileOpen"
                            @click.outside="profileOpen = false"
                            class="btn btn-ghost btn-sm w-full gap-2 justify-start"
                            :class="collapsed ? 'justify-center px-0' : ''"
                            title="Perfil">
                        <div class="avatar  placeholder shrink-0">
                            <div class="bg-primary content-center text-primary-content rounded-full w-7">
                                <span class="text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            </div>
                        </div>
                        <span x-show="!collapsed" x-transition
                              class="flex-1 text-xs font-medium truncate text-left">
                            {{ auth()->user()->name }}
                        </span>
                        <svg x-show="!collapsed" xmlns="http://www.w3.org/2000/svg"
                             class="h-3 w-3 shrink-0 text-base-content/40 transition-transform"
                             :class="profileOpen ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="profileOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute bottom-full left-0 mb-2 w-48 bg-base-100/90 backdrop-blur-md
                                border border-base-300 rounded-xl shadow-lg z-50">
                        <ul class="menu menu-sm p-2 gap-0.5">
                            <li>
                                <button @click="profileOpen = false; $dispatch('open-profile-modal', { type: 'name' })"
                                        class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Editar nome
                                </button>
                            </li>
                            <li>
                                <button @click="profileOpen = false; $dispatch('open-profile-modal', { type: 'password' })"
                                        class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                    Alterar senha
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                @endauth
            </div>

        </aside>
    </div>
</div>

@livewire(\App\Modules\Core\Livewire\UserProfile::class)
@livewireScripts
@stack('scripts')
</body>
</html>
