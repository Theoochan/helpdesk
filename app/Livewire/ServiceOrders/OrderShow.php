<?php

namespace App\Livewire\ServiceOrders;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderRead;
use App\Services\ServiceOrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class OrderShow extends Component
{
    public ServiceOrder $order;

    #[Validate('required|string|min:5')]
    public string $commentBody = '';

    public function mount(ServiceOrder $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order;
        $this->markRead();
    }

    public function start(ServiceOrderService $service): void
    {
        $this->authorize('start', $this->order);
        $service->start($this->order);
        $this->order->refresh();
        $this->markRead();
        session()->flash('success', 'OS iniciada!');
    }

    public function finish(ServiceOrderService $service): void
    {
        $this->authorize('finish', $this->order);
        $service->finish($this->order);
        $this->order->refresh();
        $this->markRead();
        session()->flash('success', 'OS finalizada!');
    }

    public function addComment(ServiceOrderService $service): void
    {
        $this->authorize('comment', $this->order);
        $this->validate();
        $service->addComment($this->order, auth()->user(), $this->commentBody);
        $this->commentBody = '';
        $this->order->refresh();
        $this->markRead();
    }

    private function markRead(): void
    {
        ServiceOrderRead::markRead(auth()->id(), $this->order->id);
    }

    public function render()
    {
        return view('livewire.service-orders.order-show', [
            'comments' => $this->order->comments()->with('user')->oldest()->get(),
        ]);
    }
}
