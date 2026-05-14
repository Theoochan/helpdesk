<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketRead;
use App\Services\TicketService;
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
        $this->authorize('view', $ticket);
        $this->ticket = $ticket;
        $this->markRead();
    }

    // ─── Ações do técnico ────────────────────────────────────────────────────

    public function assign(TicketService $service): void
    {
        $this->authorize('manage', $this->ticket);

        $service->assignTechnician($this->ticket, auth()->user());
        $this->ticket->refresh();
        $this->markRead();

        session()->flash('success', 'Chamado assumido!');
    }

    public function resolve(TicketService $service): void
    {
        $this->authorize('manage', $this->ticket);

        $service->resolveTicket($this->ticket);
        $this->ticket->refresh();
        $this->markRead();

        session()->flash('success', 'Chamado marcado como resolvido.');
    }

    // ─── Ações do colaborador dono ───────────────────────────────────────────

    public function close(TicketService $service): void
    {
        $this->authorize('close', $this->ticket);

        $service->closeTicket($this->ticket);
        $this->ticket->refresh();
        $this->markRead();

        session()->flash('success', 'Chamado fechado. Obrigado pela confirmação!');
    }

    public function cancel(TicketService $service): void
    {
        $this->authorize('cancel', $this->ticket);

        $service->cancelTicket($this->ticket);
        $this->ticket->refresh();
        $this->markRead();

        session()->flash('success', 'Chamado cancelado.');
    }

    // ─── Comentários ─────────────────────────────────────────────────────────

    public function addComment(TicketService $service): void
    {
        $this->validate();

        $isInternal = $this->isInternal && auth()->user()->isTechnician();

        $service->addComment($this->ticket, auth()->user(), $this->commentBody, $isInternal);

        $this->commentBody = '';
        $this->isInternal  = false;
        $this->ticket->refresh();
        $this->markRead();
    }

    // ─── Helper de leitura ───────────────────────────────────────────────────

    private function markRead(): void
    {
        TicketRead::markRead(auth()->id(), $this->ticket->id);
    }

    public function render()
    {
        return view('livewire.tickets.ticket-show', [
            'comments' => $this->ticket->comments()->with('user')->oldest()->get(),
        ]);
    }
}
