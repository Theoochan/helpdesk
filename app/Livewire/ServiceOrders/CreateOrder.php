<?php

namespace App\Livewire\ServiceOrders;

use App\Models\User;
use App\Services\ServiceOrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateOrder extends Component
{
    #[Validate('required|string|max:200')]
    public string $title = '';

    #[Validate('required|string|min:10')]
    public string $description = '';

    #[Validate('required|in:low,medium,high')]
    public string $priority = 'medium';

    #[Validate('required|exists:users,id')]
    public ?int $assigned_to_id = null;

    // Para o dropdown Alpine com busca
    public string $techSearch = '';

    public function mount(): void
    {
        $this->authorize('create', \App\Models\ServiceOrder::class);
    }

    public function save(ServiceOrderService $service): void
    {
        $this->validate();

        $service->create(auth()->user(), [
            'title'          => $this->title,
            'description'    => $this->description,
            'priority'       => $this->priority,
            'assigned_to_id' => $this->assigned_to_id,
        ]);

        session()->flash('success', 'OS Interna criada com sucesso!');
        $this->redirect(route('orders.index'), navigate: true);
    }

    public function render()
    {
        $technicians = User::whereIn('role', ['technician', 'admin'])
            ->where('id', '!=', auth()->id())
            ->when($this->techSearch, fn($q) => $q->where('name', 'like', "%{$this->techSearch}%"))
            ->orderBy('name')
            ->get();

        return view('livewire.service-orders.create-order', compact('technicians'));
    }
}
