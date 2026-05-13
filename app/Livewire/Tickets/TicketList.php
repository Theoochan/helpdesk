<?php

namespace App\Livewire\Tickets;

use App\Services\TicketService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TicketList extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(TicketService $service)
    {
        $user    = auth()->user();
        $filters = ['status' => $this->status ?: null];

        if ($user->isTechnician()) {
            $tickets = $service->listForTechnician($filters);
        } else {
            $tickets = $service->listForCollaborator($user, $filters);
        }

        return view('livewire.tickets.ticket-list', compact('tickets'));
    }
}
