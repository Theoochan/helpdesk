<?php

namespace Database\Factories;

use App\Modules\Core\Models\User;
use App\Modules\Chamados\Tickets\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketCommentFactory extends Factory
{
    protected $model = \App\Modules\Chamados\Tickets\Models\TicketComment::class;

    public function definition(): array
    {
        return [
            'ticket_id'   => Ticket::factory(),
            'user_id'     => User::factory(),
            'body'        => fake()->paragraph(),
            'is_internal' => fake()->boolean(20),
        ];
    }
}
