<?php

namespace Database\Seeders;

use App\Models\Category;
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
        $categories = collect([
            ['name' => 'Hardware',   'color' => '#ef4444'],
            ['name' => 'Software',   'color' => '#3b82f6'],
            ['name' => 'Rede',       'color' => '#8b5cf6'],
            ['name' => 'Impressora', 'color' => '#f59e0b'],
            ['name' => 'E-mail',     'color' => '#10b981'],
            ['name' => 'Acesso',     'color' => '#ec4899'],
            ['name' => 'VPN',        'color' => '#06b6d4'],
            ['name' => 'Outros',     'color' => '#6b7280'],
        ])->map(fn($data) => Category::create($data));

        $admin = User::factory()->technician()->create([
            'name'  => 'Técnico Admin',
            'email' => 'tecnico@helpdesk.local',
        ]);

        User::factory()->technician()->count(2)->create();

        $collaborator = User::factory()->create([
            'name'  => 'Colaborador Demo',
            'email' => 'colaborador@helpdesk.local',
        ]);

        User::factory()->count(5)->create();

        $technicians = User::where('role', 'technician')->get();

        Ticket::factory()->count(20)->make()->each(function (Ticket $ticket) use ($technicians, $categories) {
            $ticket->user_id     = User::where('role', 'collaborator')->inRandomOrder()->first()->id;
            $ticket->category_id = $categories->random()->id;

            if (in_array($ticket->status, [Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])) {
                $ticket->technician_id = $technicians->random()->id;
            }
            if ($ticket->status === Ticket::STATUS_RESOLVED || $ticket->status === Ticket::STATUS_CLOSED) {
                $ticket->resolved_at = now()->subDays(rand(1, 10));
            }
            if ($ticket->status === Ticket::STATUS_CLOSED) {
                $ticket->closed_at = now()->subDays(rand(0, 5));
            }

            $ticket->save();

            TicketComment::factory()->count(rand(1, 4))->create([
                'ticket_id' => $ticket->id,
                'user_id'   => User::inRandomOrder()->first()->id,
            ]);
        });
    }
}
