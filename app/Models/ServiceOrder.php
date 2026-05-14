<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'status', 'priority', 'requester_id', 'assigned_to_id', 'done_at'])]
class ServiceOrder extends Model
{
    use HasFactory;

    const STATUS_PENDING     = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE        = 'done';

    const PRIORITY_LOW    = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH   = 'high';

    protected function casts(): array
    {
        return [
            'done_at' => 'datetime',
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

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'Pendente',
            self::STATUS_IN_PROGRESS => 'Em Andamento',
            self::STATUS_DONE        => 'Finalizada',
            default                  => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'yellow',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_DONE        => 'green',
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
}
