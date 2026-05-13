<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function createTicket(User $author, array $data): Ticket
    {
        return Ticket::create([
            'title'       => $data['title'],
            'description' => $data['description'],
            'priority'    => $data['priority'] ?? Ticket::PRIORITY_MEDIUM,
            'category_id' => $data['category_id'] ?? null,
            'user_id'     => $author->id,
            'status'      => Ticket::STATUS_OPEN,
        ]);
    }

    public function assignTechnician(Ticket $ticket, User $technician): Ticket
    {
        $ticket->update([
            'technician_id' => $technician->id,
            'status'        => Ticket::STATUS_IN_PROGRESS,
        ]);

        return $ticket->fresh();
    }

    public function resolveTicket(Ticket $ticket): Ticket
    {
        $ticket->update([
            'status'      => Ticket::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        return $ticket->fresh();
    }

    public function closeTicket(Ticket $ticket): Ticket
    {
        $ticket->update([
            'status'    => Ticket::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return $ticket->fresh();
    }

    public function addComment(Ticket $ticket, User $author, string $body, bool $isInternal = false): TicketComment
    {
        return TicketComment::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $author->id,
            'body'        => $body,
            'is_internal' => $isInternal,
        ]);
    }

    public function listForCollaborator(User $user, array $filters = []): LengthAwarePaginator
    {
        return Ticket::query()
            ->with(['category', 'technician'])
            ->forCollaborator($user->id)
            ->byStatus($filters['status'] ?? null)
            ->byPeriod($filters['from'] ?? null, $filters['to'] ?? null)
            ->latest()
            ->paginate(15);
    }

    public function listForTechnician(array $filters = []): LengthAwarePaginator
    {
        return Ticket::query()
            ->with(['user', 'category', 'technician'])
            ->byStatus($filters['status'] ?? null)
            ->byPeriod($filters['from'] ?? null, $filters['to'] ?? null)
            ->when(isset($filters['technician_id']), fn($q) => $q->where('technician_id', $filters['technician_id']))
            ->latest()
            ->paginate(15);
    }

    public function reportByTechnician(?string $from, ?string $to): array
    {
        return Ticket::query()
            ->select('technician_id', DB::raw('count(*) as total'))
            ->with('technician:id,name')
            ->whereNotNull('technician_id')
            ->byPeriod($from, $to)
            ->groupBy('technician_id')
            ->get()
            ->toArray();
    }

    public function reportByCollaborator(?string $from, ?string $to): array
    {
        return Ticket::query()
            ->select('user_id', DB::raw('count(*) as total'))
            ->with('user:id,name')
            ->byPeriod($from, $to)
            ->groupBy('user_id')
            ->get()
            ->toArray();
    }

    public function summaryStats(): array
    {
        return [
            'open'        => Ticket::where('status', Ticket::STATUS_OPEN)->count(),
            'in_progress' => Ticket::where('status', Ticket::STATUS_IN_PROGRESS)->count(),
            'resolved'    => Ticket::where('status', Ticket::STATUS_RESOLVED)->count(),
            'closed'      => Ticket::where('status', Ticket::STATUS_CLOSED)->count(),
        ];
    }
}
