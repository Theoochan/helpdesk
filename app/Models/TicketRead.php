<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRead extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'ticket_id', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // ─── Contagem de não lidos para o navbar ─────────────────────────────────

    public static function unreadCountFor(User $user): int
    {
        return Ticket::query()
            ->leftJoin('ticket_reads', function ($join) use ($user) {
                $join->on('ticket_reads.ticket_id', '=', 'tickets.id')
                     ->where('ticket_reads.user_id', $user->id);
            })
            ->where(function ($q) {
                $q->whereNull('ticket_reads.read_at')
                  ->orWhereColumn('ticket_reads.read_at', '<', 'tickets.updated_at');
            })
            ->when(! $user->isTechnician(), fn ($q) => $q->where('tickets.user_id', $user->id))
            ->whereNotIn('tickets.status', [Ticket::STATUS_CLOSED, Ticket::STATUS_CANCELLED])
            ->count();
    }

    // ─── Upsert rápido ───────────────────────────────────────────────────────

    public static function markRead(int $userId, int $ticketId): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'ticket_id' => $ticketId],
            ['read_at' => now()]
        );
    }
}
