<?php

declare(strict_types=1);

namespace App\Modules\Chamados\Tickets\Models;

use App\Modules\Core\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'status', 'priority', 'user_id', 'technician_id', 'category_id', 'resolved_at', 'closed_at', 'due_date'])]
class Ticket extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return TicketFactory::new();
    }

    const STATUS_OPEN        = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED    = 'resolved';
    const STATUS_CLOSED      = 'closed';
    const STATUS_CANCELLED   = 'cancelled';

    const PRIORITY_LOW    = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH   = 'high';

    const ACTIVE_STATUSES     = [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED];
    const CANCELLABLE_STATUSES = [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at'   => 'datetime',
            'due_date'    => 'date',
        ];
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED, self::STATUS_CANCELLED]);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::CANCELLABLE_STATUSES);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && !$this->isTerminal() && $this->due_date->isPast();
    }

    public function dueBadge(): ?array
    {
        if (!$this->due_date || $this->isTerminal()) {
            return null;
        }
        $days = (int) now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false);
        if ($days < 0) {
            return ['label' => 'Atrasado ' . abs($days) . 'd', 'color' => 'red'];
        }
        if ($days <= 2) {
            return ['label' => $days === 0 ? 'Vence hoje' : 'Vence em ' . $days . 'd', 'color' => 'yellow'];
        }
        return ['label' => $days . 'd restantes', 'color' => 'green'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN        => 'Aberto',
            self::STATUS_IN_PROGRESS => 'Em Atendimento',
            self::STATUS_RESOLVED    => 'Resolvido',
            self::STATUS_CLOSED      => 'Fechado',
            self::STATUS_CANCELLED   => 'Cancelado',
            default                  => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN        => 'blue',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_RESOLVED    => 'green',
            self::STATUS_CLOSED      => 'gray',
            self::STATUS_CANCELLED   => 'red',
            default                  => 'gray',
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW    => 'Baixa',
            self::PRIORITY_MEDIUM => 'Média',
            self::PRIORITY_HIGH   => 'Alta',
            default               => $this->priority,
        };
    }

    public function priorityColor(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW    => 'green',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH   => 'red',
            default               => 'gray',
        };
    }

    public function scopeWithReadStatus(Builder $query, int $userId): Builder
    {
        return $query->addSelect([
            'my_read_at' => TicketRead::select('read_at')
                ->whereColumn('ticket_id', 'tickets.id')
                ->where('user_id', $userId)
                ->limit(1),
        ]);
    }

    public function scopeForCollaborator($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeByStatusOrOverdue($query, ?string $status)
    {
        if ($status === 'overdue') {
            return $query->whereNotIn('status', [self::STATUS_CLOSED, self::STATUS_CANCELLED])
                         ->whereNotNull('due_date')
                         ->whereDate('due_date', '<', now()->toDateString());
        }
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeByPeriod($query, ?string $from, ?string $to)
    {
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to)   $query->whereDate('created_at', '<=', $to);
        return $query;
    }
}
