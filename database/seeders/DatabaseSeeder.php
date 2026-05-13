<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ServiceOrder;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Categorias ────────────────────────────────────────────────────────
        $categories = collect([
            ['name' => 'Hardware',   'color' => '#ef4444'],
            ['name' => 'Software',   'color' => '#3b82f6'],
            ['name' => 'Rede',       'color' => '#8b5cf6'],
            ['name' => 'Impressora', 'color' => '#f59e0b'],
            ['name' => 'E-mail',     'color' => '#10b981'],
            ['name' => 'Acesso',     'color' => '#ec4899'],
            ['name' => 'VPN',        'color' => '#06b6d4'],
            ['name' => 'Outros',     'color' => '#6b7280'],
        ])->map(fn($d) => Category::create($d));

        // ── Usuários fixos para testes ────────────────────────────────────────
        $admin = User::factory()->admin()->create([
            'name'  => 'Admin TI',
            'email' => 'admin@helpdesk.local',
        ]);

        $tecnico = User::factory()->technician()->create([
            'name'  => 'Técnico Demo',
            'email' => 'tecnico@helpdesk.local',
        ]);

        $colaborador = User::factory()->create([
            'name'  => 'Colaborador Demo',
            'email' => 'colaborador@helpdesk.local',
        ]);

        // Usuários extras
        $tecnicos     = User::factory()->technician()->count(3)->create();
        $colaboradores = User::factory()->count(6)->create();

        $todosTecnicos     = User::whereIn('role', ['technician', 'admin'])->get();
        $todosColaboradores = User::where('role', 'collaborator')->get();

        // ── Chamados ──────────────────────────────────────────────────────────
        Ticket::factory()->count(25)->make()->each(function (Ticket $t) use ($todosTecnicos, $todosColaboradores, $categories) {
            $t->user_id     = $todosColaboradores->random()->id;
            $t->category_id = $categories->random()->id;

            if (in_array($t->status, ['in_progress', 'resolved', 'closed'])) {
                $t->technician_id = $todosTecnicos->random()->id;
            }
            if (in_array($t->status, ['resolved', 'closed'])) {
                $t->resolved_at = now()->subDays(rand(1, 15));
            }
            if ($t->status === 'closed') {
                $t->closed_at = now()->subDays(rand(0, 5));
            }
            $t->save();

            TicketComment::factory()->count(rand(0, 3))->create([
                'ticket_id' => $t->id,
                'user_id'   => User::inRandomOrder()->first()->id,
            ]);
        });

        // ── OS Internas ───────────────────────────────────────────────────────
        ServiceOrder::factory()->count(12)->make()->each(function (ServiceOrder $o) use ($todosTecnicos) {
            $pair = $todosTecnicos->shuffle()->take(2);
            $o->requester_id   = $pair->first()->id;
            $o->assigned_to_id = $pair->last()->id ?? $pair->first()->id;

            if ($o->status === 'done') {
                $o->done_at = now()->subDays(rand(1, 10));
            }
            $o->save();
        });
    }
}
