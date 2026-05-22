<?php

declare(strict_types=1);

namespace App\Modules\Chamados\Tickets\Policies;

use App\Modules\Chamados\Tickets\Models\Ticket;
use App\Modules\Core\Models\User;

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

    public function manage(User $user, ?Ticket $ticket = null): bool
    {
        return $user->isTechnician();
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id
            && $ticket->status === Ticket::STATUS_RESOLVED;
    }

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
