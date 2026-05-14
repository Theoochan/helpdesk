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

// ─── Relatórios — ranking ─────────────────────────────────────────────────────

it('relatório exibe ranking do técnico na tabela', function () {
    $tec = tecnico(['name' => 'Técnico Relatório']);
    ticket(['technician_id' => $tec->id, 'status' => 'resolved']);
    ticket(['technician_id' => $tec->id, 'status' => 'closed']);

    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->assertSee('Técnico Relatório');
});

it('relatório exibe ranking do colaborador na tabela', function () {
    $col = colaborador(['name' => 'Colaborador Relatório']);
    ticket(['user_id' => $col->id]);
    ticket(['user_id' => $col->id]);

    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->assertSee('Colaborador Relatório');
});

it('relatório filtra por período — exibe colaborador que abriu chamado recente', function () {
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
        ->set('from', now()->subDays(10)->toDateString())
        ->set('to', now()->toDateString())
        ->assertSee($col->name);
});

it('relatório mostra mensagem quando não há dados no ranking de técnicos', function () {
    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->assertSee('Nenhum dado para o período selecionado');
});

it('relatório não quebra com data "de" maior que "até"', function () {
    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->set('from', now()->toDateString())
        ->set('to', now()->subDays(10)->toDateString())
        ->assertOk();
});

// ─── Relatórios — cards de resumo ────────────────────────────────────────────

it('cards de resumo exibem contagens por status', function () {
    ticket(['status' => 'open']);
    ticket(['status' => 'open']);
    ticket(['status' => 'resolved']);
    ticket(['status' => 'cancelled']);

    Livewire::actingAs(tecnico())
        ->test(TicketReports::class)
        ->assertSee('Abertos')
        ->assertSee('Resolvidos')
        ->assertSee('Cancelados');
});

// ─── Relatórios — dados dos gráficos (service level) ─────────────────────────

it('reportByStatus retorna contagem por status', function () {
    $col = colaborador();
    ticket(['user_id' => $col->id, 'status' => 'open']);
    ticket(['user_id' => $col->id, 'status' => 'open']);
    ticket(['user_id' => $col->id, 'status' => 'resolved']);

    $service = app(\App\Services\TicketService::class);
    $result  = $service->reportByStatus(null, null);

    expect($result['open'])->toBe(2)
        ->and($result['resolved'])->toBe(1);
});

it('reportByCategory retorna agrupamento correto', function () {
    $cat = categoria(['name' => 'Infraestrutura']);
    $col = colaborador();
    ticket(['user_id' => $col->id, 'category_id' => $cat->id]);
    ticket(['user_id' => $col->id, 'category_id' => $cat->id]);
    ticket(['user_id' => $col->id, 'category_id' => null]);

    $service = app(\App\Services\TicketService::class);
    $result  = $service->reportByCategory(null, null);

    expect($result)->toHaveKey('Infraestrutura')
        ->and($result['Infraestrutura'])->toBe(2)
        ->and($result)->toHaveKey('Sem categoria');
});

it('reportTimeline preenche dias sem chamados com zero', function () {
    $service = app(\App\Services\TicketService::class);
    $result  = $service->reportTimeline(null, null); // últimos 30 dias

    expect($result['categories'])->toHaveCount(30)
        ->and($result['opened'])->toHaveCount(30)
        ->and($result['resolved'])->toHaveCount(30);
});

it('reportByTechnicianAndStatus retorna série por técnico', function () {
    $tec = tecnico(['name' => 'Técnico Alpha']);
    ticket(['technician_id' => $tec->id, 'status' => 'resolved']);
    ticket(['technician_id' => $tec->id, 'status' => 'closed']);

    $service = app(\App\Services\TicketService::class);
    $result  = $service->reportByTechnicianAndStatus(null, null);

    expect($result['techNames'])->toContain('Técnico Alpha')
        ->and($result['series'])->not->toBeEmpty();
});
