<?php

declare(strict_types=1);

namespace App\Modules\Chamados\Tickets\Livewire;

use App\Modules\Chamados\Tickets\Models\Ticket;
use App\Modules\Chamados\Tickets\Services\TicketService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TechnicianDashboard extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(TicketService $service)
    {
        $stats = $service->summaryStats();

        $recentTickets = Ticket::query()
            ->with(['user', 'category', 'technician'])
            ->byStatus($this->statusFilter ?: null)
            ->latest()
            ->paginate(10);

        return view('livewire.technician.technician-dashboard', compact('stats', 'recentTickets'));
    }
}
