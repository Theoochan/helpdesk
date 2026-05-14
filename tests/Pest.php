<?php

use App\Models\Category;
use App\Models\ServiceOrder;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// ─── Helpers de usuários ─────────────────────────────────────────────────────

function colaborador(array $attrs = []): User
{
    return User::factory()->create(array_merge(['role' => 'collaborator'], $attrs));
}

function tecnico(array $attrs = []): User
{
    return User::factory()->create(array_merge(['role' => 'technician'], $attrs));
}

function admin(array $attrs = []): User
{
    return User::factory()->create(array_merge(['role' => 'admin'], $attrs));
}

// ─── Helpers de entidades ────────────────────────────────────────────────────

function categoria(array $attrs = []): Category
{
    return Category::factory()->create($attrs);
}

function ticket(array $attrs = []): Ticket
{
    return Ticket::factory()->create(array_merge([
        'user_id'     => colaborador()->id,
        'category_id' => categoria()->id,
        'status'      => 'open',
    ], $attrs));
}

function ordem(array $attrs = []): ServiceOrder
{
    $req    = tecnico();
    $assign = tecnico();
    return ServiceOrder::factory()->create(array_merge([
        'requester_id'   => $req->id,
        'assigned_to_id' => $assign->id,
        'status'         => 'pending',
    ], $attrs));
}
