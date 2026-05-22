<?php

use App\Modules\Chamados\ServiceOrders\Livewire\CreateOrder;
use App\Modules\Chamados\ServiceOrders\Livewire\OrderList;
use App\Modules\Chamados\ServiceOrders\Livewire\OrderShow;
use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use App\Modules\Chamados\Tickets\Livewire\CategoryManager;
use App\Modules\Chamados\Tickets\Livewire\CreateTicket;
use App\Modules\Chamados\Tickets\Livewire\TechnicianDashboard;
use App\Modules\Chamados\Tickets\Livewire\TicketList;
use App\Modules\Chamados\Tickets\Livewire\TicketShow;
use App\Modules\Chamados\Tickets\Models\Ticket;
use App\Modules\Core\Livewire\Admin\AdminManager;
use App\Modules\Core\Livewire\Auth\Login;
use App\Modules\Reports\Livewire\Chamados\TicketReports;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'));

// ─── Guest ───────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
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
    Route::middleware('can:manage,' . Ticket::class)->group(function () {
        Route::get('/tecnico',     TechnicianDashboard::class)->name('technician.dashboard');
        Route::get('/relatorios',  TicketReports::class)->name('reports.index');
        Route::get('/os',          OrderList::class)->name('orders.index');
        Route::get('/os/nova',     CreateOrder::class)->name('orders.create');
        Route::get('/os/{order}',  OrderShow::class)->name('orders.show');
    });

    // ── Área exclusiva de admins ─────────────────────────────────────────────
    Route::middleware('can:manage-technicians')->group(function () {
        Route::get('/admin',      AdminManager::class)->name('admin.index');
        Route::get('/categorias', CategoryManager::class)->name('categories.index');
    });
});
