<?php

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// --- Helpers globais reutilizáveis nos testes ---

function colaborador(array $attrs = []): User
{
    return User::factory()->create(array_merge(['role' => 'collaborator'], $attrs));
}

function tecnico(array $attrs = []): User
{
    return User::factory()->create(array_merge(['role' => 'technician'], $attrs));
}

function categoria(): Category
{
    return Category::factory()->create();
}

function ticket(array $attrs = []): Ticket
{
    return Ticket::factory()->create(array_merge([
        'user_id'     => colaborador()->id,
        'category_id' => categoria()->id,
        'status'      => 'open',
    ], $attrs));
}
