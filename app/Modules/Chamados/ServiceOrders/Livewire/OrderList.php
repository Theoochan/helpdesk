<?php

declare(strict_types=1);

namespace App\Modules\Chamados\ServiceOrders\Livewire;

use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use App\Modules\Chamados\ServiceOrders\Services\ServiceOrderService;
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
        $this->authorize('viewAny', ServiceOrder::class);
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
