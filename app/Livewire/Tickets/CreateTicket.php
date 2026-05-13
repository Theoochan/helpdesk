<?php

namespace App\Livewire\Tickets;

use App\Models\Category;
use App\Models\Ticket;
use App\Services\TicketService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateTicket extends Component
{
    #[Validate('required|string|max:200')]
    public string $title = '';

    #[Validate('required|string|min:20')]
    public string $description = '';

    #[Validate('required|in:low,medium,high')]
    public string $priority = 'medium';

    #[Validate('nullable|exists:categories,id')]
    public ?int $category_id = null;

    public function save(TicketService $service): void
    {
        $this->validate();

        $service->createTicket(auth()->user(), [
            'title'       => $this->title,
            'description' => $this->description,
            'priority'    => $this->priority,
            'category_id' => $this->category_id,
        ]);

        session()->flash('success', 'Chamado aberto com sucesso!');

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tickets.create-ticket', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
