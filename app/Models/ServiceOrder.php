<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'status', 'priority', 'requester_id', 'assigned_to_id', 'done_at', 'due_date', 'transfer_requested_to_id', 'transfer_note'])]
class ServiceOrder extends Model
{
    use HasFactory;

    const STATUS_PENDING     = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE        = 'done';
    const STATUS_CANCELLED   = 'cancelled';

    const PRIORITY_LOW    = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH   = 'high';

    protected function casts(): array
    {
        return [
            'done_at'  => 'datetime',
            'due_date' => 'date',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ServiceOrderComment::class);
    }

    public function transferRequestedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transfer_requested_to_id');
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_DONE, self::STATUS_CANCELLED]);
    }

    public function hasPendingTransfer(): bool
    {
        return !is_null($this->transfer_requested_to_id);
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

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'Pendente',
            self::STATUS_IN_PROGRESS => 'Em Andamento',
            self::STATUS_DONE        => 'Finalizada',
            self::STATUS_CANCELLED   => 'Cancelada',
            default                  => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'yellow',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_DONE        => 'green',
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

    /**
     * Adiciona coluna my_read_at para indicador de novidade nas OS.
     */
    public function scopeWithReadStatus(Builder $query, int $userId): Builder
    {
        return $query->addSelect([
            'my_read_at' => ServiceOrderRead::select('read_at')
                ->whereColumn('service_order_id', 'service_orders.id')
                ->where('user_id', $userId)
                ->limit(1),
        ]);
    }

    public function scopeForTechnician($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('requester_id', $userId)
              ->orWhere('assigned_to_id', $userId);
        });
    }

    public function scopeByStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeByStatusOrOverdue($query, ?string $status)
    {
        if ($status === 'overdue') {
            return $query->whereNotIn('status', [self::STATUS_DONE, self::STATUS_CANCELLED])
                         ->whereNotNull('due_date')
                         ->whereDate('due_date', '<', now()->toDateString());
        }
        return $status ? $query->where('status', $status) : $query;
    }
}
