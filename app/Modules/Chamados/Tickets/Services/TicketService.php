<?php

declare(strict_types=1);

namespace App\Modules\Chamados\Tickets\Services;

use App\Modules\Chamados\Tickets\Models\Ticket;
use App\Modules\Chamados\Tickets\Models\TicketComment;
use App\Modules\Chamados\Tickets\Models\TicketRead;
use App\Modules\Core\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function createTicket(User $author, array $data): Ticket
    {
        $ticket = Ticket::create([
            'title'       => $data['title'],
            'description' => $data['description'],
            'priority'    => $data['priority'] ?? Ticket::PRIORITY_MEDIUM,
            'category_id' => $data['category_id'] ?? null,
            'user_id'     => $author->id,
            'status'      => Ticket::STATUS_OPEN,
        ]);

        TicketRead::markRead($author->id, $ticket->id);

        return $ticket;
    }

    public function assignTechnician(Ticket $ticket, User $technician, ?string $dueDate = null): Ticket
    {
        $ticket->update([
            'technician_id' => $technician->id,
            'status'        => Ticket::STATUS_IN_PROGRESS,
            'due_date'      => $dueDate,
        ]);

        return $ticket->fresh();
    }

    public function setDueDate(Ticket $ticket, ?string $dueDate): Ticket
    {
        $ticket->update(['due_date' => $dueDate]);
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
        abort_unless($ticket->status === Ticket::STATUS_RESOLVED, 422, 'Apenas chamados resolvidos podem ser fechados.');

        $ticket->update([
            'status'    => Ticket::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return $ticket->fresh();
    }

    public function cancelTicket(Ticket $ticket): Ticket
    {
        abort_unless($ticket->isCancellable(), 422, 'Este chamado não pode mais ser cancelado.');

        $ticket->update([
            'status'    => Ticket::STATUS_CANCELLED,
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
            ->withReadStatus($user->id)
            ->forCollaborator($user->id)
            ->byStatusOrOverdue($filters['status'] ?? null)
            ->byPeriod($filters['from'] ?? null, $filters['to'] ?? null)
            ->latest()
            ->paginate(15);
    }

    public function listForTechnician(array $filters = [], ?int $readerUserId = null): LengthAwarePaginator
    {
        return Ticket::query()
            ->with(['user', 'category', 'technician'])
            ->when($readerUserId, fn ($q) => $q->withReadStatus($readerUserId))
            ->byStatusOrOverdue($filters['status'] ?? null)
            ->byPeriod($filters['from'] ?? null, $filters['to'] ?? null)
            ->when(isset($filters['technician_id']), fn($q) => $q->where('technician_id', $filters['technician_id']))
            ->latest()
            ->paginate(15);
    }

    // ─── Relatórios ──────────────────────────────────────────────────────────

    public function reportByStatus(?string $from, ?string $to): array
    {
        return Ticket::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->byPeriod($from, $to)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function reportByCategory(?string $from, ?string $to): array
    {
        $query = Ticket::query()
            ->select(
                DB::raw("COALESCE(categories.name, 'Sem categoria') AS cat_name"),
                DB::raw('COUNT(*) AS total')
            )
            ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->groupBy(DB::raw("COALESCE(categories.name, 'Sem categoria')"))
            ->orderByDesc('total')
            ->limit(10);

        if ($from) $query->whereDate('tickets.created_at', '>=', $from);
        if ($to)   $query->whereDate('tickets.created_at', '<=', $to);

        return $query->get()
            ->mapWithKeys(fn ($r) => [$r->cat_name => (int) $r->total])
            ->toArray();
    }

    public function reportTimeline(?string $from, ?string $to): array
    {
        $start = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
        $end   = $to   ? Carbon::parse($to)->endOfDay()     : Carbon::now()->endOfDay();

        $dates = [];
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dates[$d->format('Y-m-d')] = 0;
        }

        $opened = Ticket::query()
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS total')
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'day')->toArray();

        $resolved = Ticket::query()
            ->selectRaw('DATE(resolved_at) AS day, COUNT(*) AS total')
            ->whereBetween('resolved_at', [$start, $end])
            ->whereNotNull('resolved_at')
            ->groupByRaw('DATE(resolved_at)')
            ->pluck('total', 'day')->toArray();

        $days = array_keys($dates);

        return [
            'categories' => array_map(fn ($d) => Carbon::parse($d)->format('d/m'), $days),
            'opened'     => array_map(fn ($d) => (int) ($opened[$d] ?? 0), $days),
            'resolved'   => array_map(fn ($d) => (int) ($resolved[$d] ?? 0), $days),
        ];
    }

    public function reportByTechnicianAndStatus(?string $from, ?string $to): array
    {
        $rows = Ticket::query()
            ->select('users.name AS tech_name', 'tickets.status', DB::raw('COUNT(*) AS total'))
            ->join('users', 'tickets.technician_id', '=', 'users.id')
            ->whereNotNull('tickets.technician_id')
            ->when($from, fn ($q) => $q->whereDate('tickets.created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('tickets.created_at', '<=', $to))
            ->groupBy('users.name', 'tickets.status')
            ->get();

        $byTech = [];
        foreach ($rows as $row) {
            $byTech[$row->tech_name][$row->status] = (int) $row->total;
        }

        if (empty($byTech)) {
            return ['techNames' => [], 'series' => []];
        }

        uasort($byTech, fn ($a, $b) => array_sum($b) - array_sum($a));
        $byTech = array_slice($byTech, 0, 10, true);

        $techNames    = array_keys($byTech);
        $statusConfig = [
            Ticket::STATUS_OPEN        => ['Aberto',         '#3b82f6'],
            Ticket::STATUS_IN_PROGRESS => ['Em Atendimento', '#f59e0b'],
            Ticket::STATUS_RESOLVED    => ['Resolvido',      '#22c55e'],
            Ticket::STATUS_CLOSED      => ['Fechado',        '#9ca3af'],
            Ticket::STATUS_CANCELLED   => ['Cancelado',      '#ef4444'],
        ];

        $series = [];
        foreach ($statusConfig as $status => [$label, $color]) {
            $data = array_map(fn ($n) => $byTech[$n][$status] ?? 0, $techNames);
            if (array_sum($data) > 0) {
                $series[] = ['name' => $label, 'color' => $color, 'data' => array_values($data)];
            }
        }

        return ['techNames' => $techNames, 'series' => $series];
    }

    public function reportByTechnician(?string $from, ?string $to): array
    {
        return Ticket::query()
            ->select('technician_id', DB::raw('count(*) as total'))
            ->with('technician:id,name')
            ->whereNotNull('technician_id')
            ->byPeriod($from, $to)
            ->groupBy('technician_id')
            ->get()->toArray();
    }

    public function reportByCollaborator(?string $from, ?string $to): array
    {
        return Ticket::query()
            ->select('user_id', DB::raw('count(*) as total'))
            ->with('user:id,name')
            ->byPeriod($from, $to)
            ->groupBy('user_id')
            ->get()->toArray();
    }

    public function summaryStats(): array
    {
        return [
            'open'        => Ticket::where('status', Ticket::STATUS_OPEN)->count(),
            'in_progress' => Ticket::where('status', Ticket::STATUS_IN_PROGRESS)->count(),
            'resolved'    => Ticket::where('status', Ticket::STATUS_RESOLVED)->count(),
            'closed'      => Ticket::where('status', Ticket::STATUS_CLOSED)->count(),
            'cancelled'   => Ticket::where('status', Ticket::STATUS_CANCELLED)->count(),
        ];
    }
}
