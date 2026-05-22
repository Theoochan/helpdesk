<?php

declare(strict_types=1);

namespace App\Modules\Chamados\Tickets\Models;

use App\Modules\Core\Models\User;
use Database\Factories\TicketCommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'user_id', 'body', 'is_internal'])]
class TicketComment extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return TicketCommentFactory::new();
    }

    protected $touches = ['ticket'];

    protected function casts(): array
    {
        return ['is_internal' => 'boolean'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
