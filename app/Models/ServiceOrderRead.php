<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderRead extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'service_order_id', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    // ─── Contagem de não lidos para o navbar ─────────────────────────────────

    public static function unreadCountFor(User $user): int
    {
        return ServiceOrder::query()
            ->leftJoin('service_order_reads', function ($join) use ($user) {
                $join->on('service_order_reads.service_order_id', '=', 'service_orders.id')
                     ->where('service_order_reads.user_id', $user->id);
            })
            ->where(function ($q) {
                $q->whereNull('service_order_reads.read_at')
                  ->orWhereColumn('service_order_reads.read_at', '<', 'service_orders.updated_at');
            })
            ->where(function ($q) use ($user) {
                $q->where('service_orders.requester_id', $user->id)
                  ->orWhere('service_orders.assigned_to_id', $user->id);
            })
            ->where('service_orders.status', '!=', ServiceOrder::STATUS_DONE)
            ->count();
    }

    // ─── Upsert rápido ───────────────────────────────────────────────────────

    public static function markRead(int $userId, int $serviceOrderId): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'service_order_id' => $serviceOrderId],
            ['read_at' => now()]
        );
    }
}
