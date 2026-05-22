<?php

declare(strict_types=1);

namespace App\Modules\Chamados\ServiceOrders\Policies;

use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use App\Modules\Core\Models\User;

class ServiceOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTechnician();
    }

    public function view(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin()
            || $order->requester_id === $user->id
            || $order->assigned_to_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isTechnician();
    }

    public function start(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin() || $order->assigned_to_id === $user->id;
    }

    public function finish(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin() || $order->assigned_to_id === $user->id;
    }

    public function comment(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin()
            || $order->requester_id === $user->id
            || $order->assigned_to_id === $user->id;
    }

    public function cancel(User $user, ServiceOrder $order): bool
    {
        return $order->isCancellable()
            && ($user->isAdmin() || $order->requester_id === $user->id);
    }

    public function requestTransfer(User $user, ServiceOrder $order): bool
    {
        return !$order->isTerminal()
            && $order->assigned_to_id === $user->id
            && !$order->hasPendingTransfer();
    }

    public function manageTransfer(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin() && $order->hasPendingTransfer();
    }
}
