<?php

declare(strict_types=1);

namespace App\Modules\Chamados\ServiceOrders\Livewire;

use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use App\Modules\Chamados\ServiceOrders\Services\ServiceOrderService;
use App\Modules\Core\Models\User;
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

    #[Validate('nullable|date|after_or_equal:today')]
    public ?string $due_date = null;

    public string $techSearch = '';

    public function mount(): void
    {
        $this->authorize('create', ServiceOrder::class);
    }

    public function save(ServiceOrderService $service): void
    {
        $this->validate();

        $service->create(auth()->user(), [
            'title'          => $this->title,
            'description'    => $this->description,
            'priority'       => $this->priority,
            'assigned_to_id' => $this->assigned_to_id,
            'due_date'       => $this->due_date ?: null,
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
