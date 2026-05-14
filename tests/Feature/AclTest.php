<?php

use App\Livewire\Tickets\TicketShow;
use Livewire\Livewire;

// ─── Rotas protegidas — visitante ────────────────────────────────────────────

it('visitante é redirecionado ao tentar acessar chamados', function () {
    $this->get(route('tickets.index'))->assertRedirect(route('login'));
});

it('visitante é redirecionado ao tentar criar chamado', function () {
    $this->get(route('tickets.create'))->assertRedirect(route('login'));
});

it('visitante não acessa dashboard do técnico', function () {
    $this->get(route('technician.dashboard'))->assertRedirect(route('login'));
});

it('visitante não acessa relatórios', function () {
    $this->get(route('reports.index'))->assertRedirect(route('login'));
});

// ─── Colaborador — restrições ────────────────────────────────────────────────

it('colaborador não acessa dashboard do técnico', function () {
    $this->actingAs(colaborador())
        ->get(route('technician.dashboard'))
        ->assertForbidden();
});

it('colaborador não acessa relatórios', function () {
    $this->actingAs(colaborador())
        ->get(route('reports.index'))
        ->assertForbidden();
});

it('colaborador não vê chamado de outro colaborador', function () {
    $dono  = colaborador();
    $outro = colaborador();
    $t     = ticket(['user_id' => $dono->id]);

    Livewire::actingAs($outro)
        ->test(TicketShow::class, ['ticket' => $t])
        ->assertForbidden();
});

it('colaborador vê apenas seus próprios chamados na listagem', function () {
    $eu    = colaborador();
    $outro = colaborador();
    $meu   = ticket(['user_id' => $eu->id,    'title' => 'Meu chamado']);
    $alheio = ticket(['user_id' => $outro->id, 'title' => 'Chamado alheio']);

    $this->actingAs($eu)
        ->get(route('tickets.index'))
        ->assertSee('Meu chamado')
        ->assertDontSee('Chamado alheio');
});

it('colaborador não consegue assumir um chamado via Livewire', function () {
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('assign')
        ->assertForbidden();
});

it('colaborador não consegue resolver um chamado via Livewire', function () {
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('resolve')
        ->assertForbidden();
});

// ─── Técnico — permissões ────────────────────────────────────────────────────

it('técnico acessa dashboard', function () {
    $this->actingAs(tecnico())
        ->get(route('technician.dashboard'))
        ->assertOk();
});

it('técnico acessa relatórios', function () {
    $this->actingAs(tecnico())
        ->get(route('reports.index'))
        ->assertOk();
});

it('técnico vê chamados de qualquer colaborador', function () {
    $c1 = colaborador();
    $c2 = colaborador();
    ticket(['user_id' => $c1->id, 'title' => 'Chamado A']);
    ticket(['user_id' => $c2->id, 'title' => 'Chamado B']);

    $this->actingAs(tecnico())
        ->get(route('tickets.index'))
        ->assertSee('Chamado A')
        ->assertSee('Chamado B');
});

it('técnico vê chamado de qualquer colaborador na tela de detalhe', function () {
    $t = ticket(['user_id' => colaborador()->id]);

    Livewire::actingAs(tecnico())
        ->test(TicketShow::class, ['ticket' => $t])
        ->assertOk();
});

it('técnico não consegue fechar chamado via Livewire', function () {
    $tec  = tecnico();
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id, 'status' => 'resolved', 'technician_id' => $tec->id]);

    Livewire::actingAs($tec)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('close')
        ->assertForbidden();
});

it('técnico não consegue cancelar chamado via Livewire', function () {
    $tec  = tecnico();
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id, 'status' => 'open']);

    Livewire::actingAs($tec)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('cancel')
        ->assertForbidden();
});

it('colaborador pode cancelar chamado resolvido (ainda não fechado)', function () {
    $tec  = tecnico();
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id, 'status' => 'resolved', 'technician_id' => $tec->id]);

    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('cancel')
        ->assertHasNoErrors();

    expect($t->fresh()->status)->toBe('cancelled');
});

it('colaborador não pode cancelar chamado já fechado', function () {
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id, 'status' => 'closed']);

    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('cancel')
        ->assertForbidden();
});

it('colaborador não pode cancelar chamado já cancelado', function () {
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id, 'status' => 'cancelled']);

    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('cancel')
        ->assertForbidden();
});
