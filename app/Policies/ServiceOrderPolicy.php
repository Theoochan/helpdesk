<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    /** Apenas técnicos e admins enxergam OS internas */
    public function viewAny(User $user): bool
    {
        return $user->isTechnician();
    }

    /** Pode ver se for o solicitante, o responsável, ou admin */
    public function view(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin()
            || $order->requester_id === $user->id
            || $order->assigned_to_id === $user->id;
    }

    /** Qualquer técnico pode criar */
    public function create(User $user): bool
    {
        return $user->isTechnician();
    }

    /** Pode iniciar se for o responsável ou admin */
    public function start(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin() || $order->assigned_to_id === $user->id;
    }

    /** Pode finalizar se for o responsável ou admin */
    public function finish(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin() || $order->assigned_to_id === $user->id;
    }

    /** Pode comentar se participar da OS */
    public function comment(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin()
            || $order->requester_id === $user->id
            || $order->assigned_to_id === $user->id;
    }

    /** Cancela: solicitante ou admin, enquanto não finalizada */
    public function cancel(User $user, ServiceOrder $order): bool
    {
        return $order->isCancellable()
            && ($user->isAdmin() || $order->requester_id === $user->id);
    }

    /** Solicitar transferência: apenas o responsável atual, OS não finalizada */
    public function requestTransfer(User $user, ServiceOrder $order): bool
    {
        return !$order->isTerminal()
            && $order->assigned_to_id === $user->id
            && !$order->hasPendingTransfer();
    }

    /** Aprovar ou rejeitar transferência: apenas admin */
    public function manageTransfer(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin() && $order->hasPendingTransfer();
    }
}
