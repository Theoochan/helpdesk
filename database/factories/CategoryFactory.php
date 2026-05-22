<?php

namespace Database\Factories;

use App\Modules\Chamados\Tickets\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name'  => fake()->unique()->word(),
            'color' => fake()->hexColor(),
        ];
    }
}
