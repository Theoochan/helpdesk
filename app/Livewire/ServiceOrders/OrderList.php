<?php

namespace App\Livewire\ServiceOrders;

use App\Services\ServiceOrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class OrderList extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $tab = 'mine';

    public function mount(): void
    {
        $this->authorize('viewAny', \App\Models\ServiceOrder::class);
    }

    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingTab(): void    { $this->resetPage(); }

    public function render(ServiceOrderService $service)
    {
        $user    = auth()->user();
        $filters = ['status' => $this->status ?: null];

        $orders = ($this->tab === 'all')
            ? $service->listAll($user, $filters)
            : $service->listForTechnician($user, $filters);

        return view('livewire.service-orders.order-list', compact('orders'));
    }
}
