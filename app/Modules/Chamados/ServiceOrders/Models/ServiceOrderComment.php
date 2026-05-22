<?php

declare(strict_types=1);

namespace App\Modules\Chamados\ServiceOrders\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_order_id', 'user_id', 'body'])]
class ServiceOrderComment extends Model
{
    use HasFactory;

    protected $touches = ['serviceOrder'];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
