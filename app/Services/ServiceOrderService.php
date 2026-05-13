<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderComment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceOrderService
{
    public function create(User $requester, array $data): ServiceOrder
    {
        return ServiceOrder::create([
            'title'          => $data['title'],
            'description'    => $data['description'],
            'priority'       => $data['priority'] ?? ServiceOrder::PRIORITY_MEDIUM,
            'assigned_to_id' => $data['assigned_to_id'],
            'requester_id'   => $requester->id,
            'status'         => ServiceOrder::STATUS_PENDING,
        ]);
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
            ->forTechnician($user->id)
            ->byStatus($filters['status'] ?? null)
            ->latest()
            ->paginate(15);
    }

    public function listAll(array $filters = []): LengthAwarePaginator
    {
        return ServiceOrder::query()
            ->with(['requester', 'assignedTo'])
            ->byStatus($filters['status'] ?? null)
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
