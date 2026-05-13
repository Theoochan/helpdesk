<?php

use App\Livewire\Reports\TicketReports;
use App\Livewire\Technician\TechnicianDashboard;
use App\Models\Ticket;
use Livewire\Livewire;

// ─── Dashboard do técnico ────────────────────────────────────────────────────

it('dashboard exibe contagens corretas por status', function () {
    ticket(['status' => 'open']);
    ticket(['status' => 'open']);
    ticket(['status' => 'in_progress']);
    ticket(['status' => 'resolved']);

    Livewire::actingAs(tecnico())
        ->test(TechnicianDashboard::class)
        ->assertSeeInOrder(['2', '1', '1']);
});

it('filtro de status no dashboard mostra apenas chamados do status selecionado', function () {
    // Títulos únicos que não coincidem com labels de status dos cards
    ticket(['status' => 'open',        'title' => 'Ticket-ABERTO-XYZ']);
    ticket(['status' => 'in_progress', 'title' => 'Ticket-ANDAMENTO-XYZ']);

    Livewire::actingAs(tecnico())
        ->test(TechnicianDashboard::class)
        ->set('statusFilter', 'open')
        ->assertSee('Ticket-ABERTO-XYZ')
        ->assertDontSee('Ticket-ANDAMENTO-XYZ');
});

it('limpar filtro no dashboard exibe todos os chamados', function () {
    ticket(['status' => 'open',        'title' => 'Aberto']);
    ticket(['status' => 'in_progress', 'title' => 'Em Atendimento']);

    Livewire::actingAs(tecnico())
        ->test(TechnicianDashboard::class)
        ->set('statusFilter', 'open')
        ->set('statusFilter', '')
        ->assertSee('Aberto')
        ->assertSee('Em Atendimento');
});

// ─── Relatórios ──────────────────────────────────────────────────────────────

it('relatório agrupa chamados por técnico corretamente', function () {
    $tec = tecnico(['name' => 'Técnico Relatório']);
    ticket(['technician_id' => $tec->id, 'status' => 'resolved']);
    ticket(['technician_id' => $tec->id, 'status' => 'closed']);

    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->set('groupBy', 'technician')
        ->assertSee('Técnico Relatório');
});

it('relatório agrupa chamados por colaborador corretamente', function () {
    $col = colaborador(['name' => 'Colaborador Relatório']);
    ticket(['user_id' => $col->id]);
    ticket(['user_id' => $col->id]);

    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->set('groupBy', 'collaborator')
        ->assertSee('Colaborador Relatório');
});

it('relatório filtra por período', function () {
    $col = colaborador();

    // Chamado antigo — fora do período
    Ticket::factory()->create([
        'user_id'    => $col->id,
        'title'      => 'Chamado Antigo',
        'created_at' => now()->subDays(60),
    ]);

    // Chamado recente — dentro do período
    Ticket::factory()->create([
        'user_id'    => $col->id,
        'title'      => 'Chamado Recente',
        'created_at' => now()->subDays(5),
    ]);

    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->set('groupBy', 'collaborator')
        ->set('from', now()->subDays(10)->toDateString())
        ->set('to', now()->toDateString())
        ->assertSee($col->name);
});

it('relatório mostra mensagem quando não há dados', function () {
    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->set('groupBy', 'technician')
        ->assertSee('Nenhum dado para o período selecionado');
});

it('relatório não quebra com data "de" maior que "até"', function () {
    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->set('from', now()->toDateString())
        ->set('to', now()->subDays(10)->toDateString())
        ->assertOk();
});
