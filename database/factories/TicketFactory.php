<?php

namespace Database\Factories;

use App\Modules\Core\Models\User;
use App\Modules\Chamados\Tickets\Models\Category;
use App\Modules\Chamados\Tickets\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(6),
            'description' => fake()->paragraph(3),
            'status'      => fake()->randomElement([
                Ticket::STATUS_OPEN,
                Ticket::STATUS_IN_PROGRESS,
                Ticket::STATUS_RESOLVED,
                Ticket::STATUS_CLOSED,
            ]),
            'priority'    => fake()->randomElement([
                Ticket::PRIORITY_LOW,
                Ticket::PRIORITY_MEDIUM,
                Ticket::PRIORITY_HIGH,
            ]),
            'user_id'     => User::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
