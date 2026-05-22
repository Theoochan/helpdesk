<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use App\Modules\Chamados\ServiceOrders\Models\ServiceOrderComment;
use App\Modules\Chamados\Tickets\Models\Ticket;
use App\Modules\Chamados\Tickets\Models\TicketComment;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── Role helpers ────────────────────────────────────────────────────────

    public function isCollaborator(): bool
    {
        return $this->role === 'collaborator';
    }

    public function isTechnician(): bool
    {
        return in_array($this->role, ['technician', 'admin']);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ─── Relacionamentos — Tickets ───────────────────────────────────────────

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'technician_id');
    }

    public function ticketComments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    // ─── Relacionamentos — OS Internas ───────────────────────────────────────

    public function requestedOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'requester_id');
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'assigned_to_id');
    }

    public function serviceOrderComments(): HasMany
    {
        return $this->hasMany(ServiceOrderComment::class);
    }
}
