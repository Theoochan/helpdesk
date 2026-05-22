<?php

declare(strict_types=1);

namespace App\Modules\Chamados\ServiceOrders\Livewire;

use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use App\Modules\Chamados\ServiceOrders\Models\ServiceOrderRead;
use App\Modules\Chamados\ServiceOrders\Services\ServiceOrderService;
use App\Modules\Core\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class OrderShow extends Component
{
    public ServiceOrder $order;

    #[Validate('required|string|min:5')]
    public string $commentBody = '';

    #[Validate('nullable|exists:users,id')]
    public ?int $transferTo = null;

    #[Validate('nullable|string|max:500')]
    public ?string $transferNote = null;

    public bool $showTransferForm = false;

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

    public function cancel(ServiceOrderService $service): void
    {
        $this->authorize('cancel', $this->order);
        $service->cancel($this->order);
        $this->order->refresh();
        $this->markRead();
        session()->flash('success', 'OS cancelada.');
    }

    public function requestTransfer(ServiceOrderService $service): void
    {
        $this->authorize('requestTransfer', $this->order);
        $this->validateOnly('transferTo');

        abort_if(!$this->transferTo, 422, 'Selecione o técnico.');
        $target = User::findOrFail($this->transferTo);

        $service->requestTransfer($this->order, $target, $this->transferNote ?: null);
        $this->showTransferForm = false;
        $this->transferTo       = null;
        $this->transferNote     = null;
        $this->order->refresh();
        $this->markRead();
        session()->flash('success', 'Solicitação de transferência enviada ao admin.');
    }

    public function approveTransfer(ServiceOrderService $service): void
    {
        $this->authorize('manageTransfer', $this->order);
        $service->approveTransfer($this->order);
        $this->order->refresh();
        $this->markRead();
        session()->flash('success', 'Transferência aprovada.');
    }

    public function rejectTransfer(ServiceOrderService $service): void
    {
        $this->authorize('manageTransfer', $this->order);
        $service->rejectTransfer($this->order);
        $this->order->refresh();
        $this->markRead();
        session()->flash('success', 'Transferência rejeitada.');
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
        $technicians = User::whereIn('role', ['technician', 'admin'])
            ->where('id', '!=', $this->order->assigned_to_id)
            ->orderBy('name')
            ->get();

        return view('livewire.service-orders.order-show', [
            'comments'    => $this->order->comments()->with('user')->oldest()->get(),
            'technicians' => $technicians,
        ]);
    }
}
