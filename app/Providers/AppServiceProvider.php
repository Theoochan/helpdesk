<?php

namespace App\Providers;

use App\Models\ServiceOrder;
use App\Models\Ticket;
use App\Policies\ServiceOrderPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Policies
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(ServiceOrder::class, ServiceOrderPolicy::class);

        // Gate exclusivo para área admin
        Gate::define('manage-technicians', fn ($user) => $user->isAdmin());
    }
}
