<?php

use App\Livewire\Tickets\CreateTicket;
use App\Livewire\Tickets\TicketShow;
use App\Models\Ticket;
use Livewire\Livewire;

// ─── Criação ────────────────────────────────────────────────────────────────

it('colaborador cria chamado com dados válidos', function () {
    $user = colaborador();
    $cat  = categoria();

    Livewire::actingAs($user)
        ->test(CreateTicket::class)
        ->set('title', 'Computador não liga')
        ->set('description', 'O computador não liga desde esta manhã. Já tentei religar várias vezes.')
        ->set('priority', 'high')
        ->set('category_id', $cat->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('tickets', [
        'title'   => 'Computador não liga',
        'user_id' => $user->id,
        'status'  => 'open',
        'priority' => 'high',
    ]);
});

it('não cria chamado sem título', function () {
    Livewire::actingAs(colaborador())
        ->test(CreateTicket::class)
        ->set('title', '')
        ->set('description', 'Descrição com mais de vinte caracteres aqui.')
        ->call('save')
        ->assertHasErrors(['title']);
});

it('não cria chamado com descrição curta (menos de 20 chars)', function () {
    Livewire::actingAs(colaborador())
        ->test(CreateTicket::class)
        ->set('title', 'Título válido')
        ->set('description', 'Curta demais')
        ->call('save')
        ->assertHasErrors(['description']);
});

it('não cria chamado com prioridade inválida', function () {
    Livewire::actingAs(colaborador())
        ->test(CreateTicket::class)
        ->set('title', 'Título válido')
        ->set('description', 'Descrição com mais de vinte caracteres aqui.')
        ->set('priority', 'urgente')
        ->call('save')
        ->assertHasErrors(['priority']);
});

it('cria chamado sem categoria (campo opcional)', function () {
    Livewire::actingAs(colaborador())
        ->test(CreateTicket::class)
        ->set('title', 'Chamado sem categoria')
        ->set('description', 'Descrição com mais de vinte caracteres aqui.')
        ->set('category_id', null)
        ->call('save')
        ->assertHasNoErrors();
});

// ─── Fluxo de status completo ────────────────────────────────────────────────

it('fluxo completo: aberto → em atendimento → resolvido → fechado', function () {
    $tec  = tecnico();
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id, 'status' => 'open']);

    // Técnico assume
    Livewire::actingAs($tec)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('assign');

    $t->refresh();
    expect($t->status)->toBe('in_progress')
        ->and($t->technician_id)->toBe($tec->id);

    // Técnico resolve
    Livewire::actingAs($tec)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('resolve');

    $t->refresh();
    expect($t->status)->toBe('resolved')
        ->and($t->resolved_at)->not->toBeNull();

    // Colaborador fecha
    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('close');

    $t->refresh();
    expect($t->status)->toBe('closed')
        ->and($t->closed_at)->not->toBeNull();
});

it('técnico pode fechar chamado resolvido diretamente', function () {
    $tec = tecnico();
    $t   = ticket(['status' => 'resolved', 'technician_id' => $tec->id]);

    Livewire::actingAs($tec)
        ->test(TicketShow::class, ['ticket' => $t])
        ->call('close');

    expect($t->fresh()->status)->toBe('closed');
});

// ─── Comentários ────────────────────────────────────────────────────────────

it('colaborador adiciona comentário no próprio chamado', function () {
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->set('commentBody', 'Ainda está acontecendo o problema.')
        ->call('addComment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ticket_comments', [
        'ticket_id' => $t->id,
        'user_id'   => $user->id,
        'body'      => 'Ainda está acontecendo o problema.',
    ]);
});

it('não adiciona comentário vazio', function () {
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->set('commentBody', '')
        ->call('addComment')
        ->assertHasErrors(['commentBody']);
});

it('técnico pode marcar comentário como nota interna', function () {
    $tec = tecnico();
    $t   = ticket();

    Livewire::actingAs($tec)
        ->test(TicketShow::class, ['ticket' => $t])
        ->set('commentBody', 'Nota interna para a equipe.')
        ->set('isInternal', true)
        ->call('addComment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ticket_comments', [
        'ticket_id'   => $t->id,
        'is_internal' => true,
    ]);
});

it('colaborador não consegue criar nota interna', function () {
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->set('commentBody', 'Tentando nota interna.')
        ->set('isInternal', true)
        ->call('addComment');

    // Mesmo com isInternal=true, é salvo como false para colaboradores
    $this->assertDatabaseHas('ticket_comments', [
        'ticket_id'   => $t->id,
        'is_internal' => false,
    ]);
});

it('não permite comentar em chamado fechado', function () {
    $user = colaborador();
    $t    = ticket(['user_id' => $user->id, 'status' => 'closed']);

    // O formulário não é exibido, mas testamos a camada de apresentação
    Livewire::actingAs($user)
        ->test(TicketShow::class, ['ticket' => $t])
        ->assertDontSee('Comentar');
});
