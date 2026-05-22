<?php

declare(strict_types=1);

namespace App\Modules\Chamados\ServiceOrders\Services;

use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use App\Modules\Chamados\ServiceOrders\Models\ServiceOrderComment;
use App\Modules\Chamados\ServiceOrders\Models\ServiceOrderRead;
use App\Modules\Core\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceOrderService
{
    public function create(User $requester, array $data): ServiceOrder
    {
        $order = ServiceOrder::create([
            'title'          => $data['title'],
            'description'    => $data['description'],
            'priority'       => $data['priority'] ?? ServiceOrder::PRIORITY_MEDIUM,
            'assigned_to_id' => $data['assigned_to_id'],
            'requester_id'   => $requester->id,
            'status'         => ServiceOrder::STATUS_PENDING,
            'due_date'       => $data['due_date'] ?? null,
        ]);

        ServiceOrderRead::markRead($requester->id, $order->id);

        return $order;
    }

    public function start(ServiceOrder $order): ServiceOrder
    {
        $order->update(['status' => ServiceOrder::STATUS_IN_PROGRESS]);
        return $order->fresh();
    }

    public function finish(ServiceOrder $order): ServiceOrder
    {
        $order->update([
            'status'  => ServiceOrder::STATUS_DONE,
            'done_at' => now(),
        ]);
        return $order->fresh();
    }

    public function cancel(ServiceOrder $order): ServiceOrder
    {
        $order->update([
            'status'                   => ServiceOrder::STATUS_CANCELLED,
            'transfer_requested_to_id' => null,
            'transfer_note'            => null,
        ]);
        return $order->fresh();
    }

    public function requestTransfer(ServiceOrder $order, User $requestedTo, ?string $note = null): ServiceOrder
    {
        $order->update([
            'transfer_requested_to_id' => $requestedTo->id,
            'transfer_note'            => $note,
        ]);
        return $order->fresh();
    }

    public function approveTransfer(ServiceOrder $order): ServiceOrder
    {
        $order->update([
            'assigned_to_id'           => $order->transfer_requested_to_id,
            'transfer_requested_to_id' => null,
            'transfer_note'            => null,
        ]);
        return $order->fresh();
    }

    public function rejectTransfer(ServiceOrder $order): ServiceOrder
    {
        $order->update([
            'transfer_requested_to_id' => null,
            'transfer_note'            => null,
        ]);
        return $order->fresh();
    }

    public function addComment(ServiceOrder $order, User $author, string $body): ServiceOrderComment
    {
        return ServiceOrderComment::create([
            'service_order_id' => $order->id,
            'user_id'          => $author->id,
            'body'             => $body,
        ]);
    }

    public function listForTechnician(User $user, array $filters = []): LengthAwarePaginator
    {
        return ServiceOrder::query()
            ->with(['requester', 'assignedTo'])
            ->withReadStatus($user->id)
            ->forTechnician($user->id)
            ->byStatusOrOverdue($filters['status'] ?? null)
            ->latest()
            ->paginate(15);
    }

    public function listAll(User $reader, array $filters = []): LengthAwarePaginator
    {
        return ServiceOrder::query()
            ->with(['requester', 'assignedTo'])
            ->withReadStatus($reader->id)
            ->byStatusOrOverdue($filters['status'] ?? null)
            ->latest()
            ->paginate(15);
    }

    public function summaryStats(User $user): array
    {
        $base = ServiceOrder::forTechnician($user->id);

        return [
            'pending'     => (clone $base)->where('status', ServiceOrder::STATUS_PENDING)->count(),
            'in_progress' => (clone $base)->where('status', ServiceOrder::STATUS_IN_PROGRESS)->count(),
            'done'        => (clone $base)->where('status', ServiceOrder::STATUS_DONE)->count(),
        ];
    }
}
