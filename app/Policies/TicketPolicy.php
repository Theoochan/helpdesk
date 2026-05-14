<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isTechnician() || $ticket->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCollaborator();
    }

    /** Ações exclusivas de técnico: assumir e resolver */
    public function manage(User $user, ?Ticket $ticket = null): bool
    {
        return $user->isTechnician();
    }

    /** Fechar: apenas o colaborador dono, somente quando resolvido */
    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id
            && $ticket->status === Ticket::STATUS_RESOLVED;
    }

    /** Cancelar: apenas o colaborador dono, enquanto não estiver terminal */
    public function cancel(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id
            && $ticket->isCancellable();
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isTechnician() || $ticket->user_id === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isTechnician();
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->isTechnician();
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
