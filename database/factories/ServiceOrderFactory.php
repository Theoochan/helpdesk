<?php

namespace Database\Factories;

use App\Modules\Core\Models\User;
use App\Modules\Chamados\ServiceOrders\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        return [
            'title'          => fake()->sentence(5),
            'description'    => fake()->paragraph(2),
            'status'         => fake()->randomElement([
                ServiceOrder::STATUS_PENDING,
                ServiceOrder::STATUS_IN_PROGRESS,
                ServiceOrder::STATUS_DONE,
            ]),
            'priority'       => fake()->randomElement([
                ServiceOrder::PRIORITY_LOW,
                ServiceOrder::PRIORITY_MEDIUM,
                ServiceOrder::PRIORITY_HIGH,
            ]),
            'requester_id'   => User::factory()->technician(),
            'assigned_to_id' => User::factory()->technician(),
        ];
    }
}
