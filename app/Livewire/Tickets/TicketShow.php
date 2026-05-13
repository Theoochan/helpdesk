<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class TicketShow extends Component
{
    public Ticket $ticket;

    #[Validate('required|string|min:5')]
    public string $commentBody = '';

    public bool $isInternal = false;

    public function mount(Ticket $ticket): void
    {
        $user = auth()->user();

        if ($user->isCollaborator() && $ticket->user_id !== $user->id) {
            abort(403);
        }

        $this->ticket = $ticket;
    }

    public function addComment(TicketService $service): void
    {
        $this->validate();

        $isInternal = $this->isInternal && auth()->user()->isTechnician();

        $service->addComment($this->ticket, auth()->user(), $this->commentBody, $isInternal);

        $this->commentBody = '';
        $this->isInternal  = false;

        $this->ticket->refresh();
    }

    public function assign(TicketService $service): void
    {
        $this->authorize('manage', $this->ticket);

        $service->assignTechnician($this->ticket, auth()->user());

        $this->ticket->refresh();

        session()->flash('success', 'Chamado assumido com sucesso!');
    }

    public function resolve(TicketService $service): void
    {
        $this->authorize('manage', $this->ticket);

        $service->resolveTicket($this->ticket);

        $this->ticket->refresh();

        session()->flash('success', 'Chamado marcado como resolvido!');
    }

    public function close(TicketService $service): void
    {
        $user = auth()->user();

        if ($user->isCollaborator() && $this->ticket->user_id !== $user->id) {
            abort(403);
        }

        if ($user->isTechnician()) {
            $this->authorize('manage', $this->ticket);
        }

        $service->closeTicket($this->ticket);

        $this->ticket->refresh();

        session()->flash('success', 'Chamado fechado.');
    }

    public function render()
    {
        return view('livewire.tickets.ticket-show', [
            'comments' => $this->ticket->comments()->with('user')->oldest()->get(),
        ]);
    }
}
