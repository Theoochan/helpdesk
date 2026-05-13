<?php

use App\Livewire\Admin\TechnicianManager;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Reports\TicketReports;
use App\Livewire\ServiceOrders\CreateOrder;
use App\Livewire\ServiceOrders\OrderList;
use App\Livewire\ServiceOrders\OrderShow;
use App\Livewire\Technician\TechnicianDashboard;
use App\Livewire\Tickets\CreateTicket;
use App\Livewire\Tickets\TicketList;
use App\Livewire\Tickets\TicketShow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'));

// ─── Guest ───────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

// ─── Autenticado ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return auth()->user()->isTechnician()
            ? redirect()->route('technician.dashboard')
            : redirect()->route('tickets.index');
    })->name('dashboard');

    // ── Chamados (colaboradores e técnicos) ──────────────────────────────────
    Route::get('/chamados',          TicketList::class)->name('tickets.index');
    Route::get('/chamados/novo',     CreateTicket::class)->name('tickets.create');
    Route::get('/chamados/{ticket}', TicketShow::class)->name('tickets.show');

    // ── Área exclusiva de técnicos ───────────────────────────────────────────
    Route::middleware('can:manage,App\Models\Ticket')->group(function () {
        Route::get('/tecnico',     TechnicianDashboard::class)->name('technician.dashboard');
        Route::get('/relatorios',  TicketReports::class)->name('reports.index');
        Route::get('/os',          OrderList::class)->name('orders.index');
        Route::get('/os/nova',     CreateOrder::class)->name('orders.create');
        Route::get('/os/{order}',  OrderShow::class)->name('orders.show');
    });

    // ── Área exclusiva de admins ─────────────────────────────────────────────
    Route::middleware('can:manage-technicians')->group(function () {
        Route::get('/admin/tecnicos', TechnicianManager::class)->name('admin.technicians');
    });
});
