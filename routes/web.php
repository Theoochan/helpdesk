<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Reports\TicketReports;
use App\Livewire\Technician\TechnicianDashboard;
use App\Livewire\Tickets\CreateTicket;
use App\Livewire\Tickets\TicketList;
use App\Livewire\Tickets\TicketShow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'));

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

Route::middleware('auth')->group(function () {

    // Dashboard adaptativo por papel
    Route::get('/dashboard', function () {
        return auth()->user()->isTechnician()
            ? redirect()->route('technician.dashboard')
            : redirect()->route('tickets.index');
    })->name('dashboard');

    // Técnico
    Route::get('/tecnico', TechnicianDashboard::class)
        ->name('technician.dashboard')
        ->middleware('can:manage,App\Models\Ticket');

    Route::get('/relatorios', TicketReports::class)
        ->name('reports.index')
        ->middleware('can:manage,App\Models\Ticket');

    // Chamados (ambos os papéis)
    Route::get('/chamados',          TicketList::class)->name('tickets.index');
    Route::get('/chamados/novo',     CreateTicket::class)->name('tickets.create');
    Route::get('/chamados/{ticket}', TicketShow::class)->name('tickets.show');
});
