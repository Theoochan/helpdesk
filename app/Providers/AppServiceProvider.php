<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use App\Modules\Chamados\ServiceOrders\Policies\ServiceOrderPolicy;
use App\Modules\Chamados\Tickets\Models\Ticket;
use App\Modules\Chamados\Tickets\Policies\TicketPolicy;
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
