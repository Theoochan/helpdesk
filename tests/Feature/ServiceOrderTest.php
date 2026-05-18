<?php

use App\Livewire\ServiceOrders\CreateOrder;
use App\Livewire\ServiceOrders\OrderList;
use App\Livewire\ServiceOrders\OrderShow;
use App\Models\ServiceOrder;
use Livewire\Livewire;

// ─── ACL: quem pode acessar OS ───────────────────────────────────────────────

it('colaborador não acessa lista de OS', function () {
    $this->actingAs(colaborador())
        ->get(route('orders.index'))
        ->assertForbidden();
});

it('colaborador não acessa formulário de OS', function () {
    $this->actingAs(colaborador())
        ->get(route('orders.create'))
        ->assertForbidden();
});

it('técnico acessa lista de OS', function () {
    $this->actingAs(tecnico())
        ->get(route('orders.index'))
        ->assertOk();
});

it('técnico vê apenas suas OS na aba mine', function () {
    $eu      = tecnico();
    $outro   = tecnico();
    $terceiro = tecnico();

    ServiceOrder::factory()->create([
        'requester_id'   => $eu->id,
        'assigned_to_id' => $outro->id,
        'title'          => 'OS-MINHA-XYZ',
    ]);

    ServiceOrder::factory()->create([
        'requester_id'   => $outro->id,
        'assigned_to_id' => $terceiro->id,
        'title'          => 'OS-ALHEIA-XYZ',
    ]);

    Livewire::actingAs($eu)
        ->test(OrderList::class)
        ->set('tab', 'mine')
        ->assertSee('OS-MINHA-XYZ')
        ->assertDontSee('OS-ALHEIA-XYZ');
});

it('técnico vê todas as OS na aba all (somente leitura)', function () {
    $eu      = tecnico();
    $outro   = tecnico();
    $terceiro = tecnico();

    ServiceOrder::factory()->create([
        'requester_id'   => $eu->id,
        'assigned_to_id' => $outro->id,
        'title'          => 'OS-MINHA-XYZ',
    ]);

    ServiceOrder::factory()->create([
        'requester_id'   => $outro->id,
        'assigned_to_id' => $terceiro->id,
        'title'          => 'OS-ALHEIA-XYZ',
    ]);

    Livewire::actingAs($eu)
        ->test(OrderList::class)
        ->set('tab', 'all')
        ->assertSee('OS-MINHA-XYZ')
        ->assertSee('OS-ALHEIA-XYZ');
});

it('admin vê todas as OS independente de participação', function () {
    $t1 = tecnico();
    $t2 = tecnico();
    ServiceOrder::factory()->create(['requester_id' => $t1->id, 'assigned_to_id' => $t2->id, 'title' => 'OS-A']);
    ServiceOrder::factory()->create(['requester_id' => $t2->id, 'assigned_to_id' => $t1->id, 'title' => 'OS-B']);

    Livewire::actingAs(admin())
        ->test(OrderList::class)
        ->set('tab', 'all')
        ->assertSee('OS-A')
        ->assertSee('OS-B');
});

it('técnico não acessa OS que não participa', function () {
    $t1 = tecnico();
    $t2 = tecnico();
    $intruso = tecnico();
    $os = ordem(['requester_id' => $t1->id, 'assigned_to_id' => $t2->id]);

    Livewire::actingAs($intruso)
        ->test(OrderShow::class, ['order' => $os])
        ->assertForbidden();
});

// ─── Criação de OS ───────────────────────────────────────────────────────────

it('técnico cria OS com dados válidos', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();

    Livewire::actingAs($solicitante)
        ->test(CreateOrder::class)
        ->set('title', 'Troca de memória RAM')
        ->set('description', 'Servidor 03 precisa de upgrade de memória.')
        ->set('priority', 'high')
        ->set('assigned_to_id', $responsavel->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('orders.index'));

    $this->assertDatabaseHas('service_orders', [
        'title'          => 'Troca de memória RAM',
        'requester_id'   => $solicitante->id,
        'assigned_to_id' => $responsavel->id,
        'status'         => 'pending',
    ]);
});

it('técnico cria OS com prazo', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $dueDate     = now()->addDays(7)->toDateString();

    Livewire::actingAs($solicitante)
        ->test(CreateOrder::class)
        ->set('title', 'OS com prazo definido')
        ->set('description', 'Descrição detalhada da tarefa.')
        ->set('priority', 'medium')
        ->set('assigned_to_id', $responsavel->id)
        ->set('due_date', $dueDate)
        ->call('save')
        ->assertHasNoErrors();

    $os = ServiceOrder::where('title', 'OS com prazo definido')->first();
    expect($os)->not->toBeNull()
        ->and($os->due_date->toDateString())->toBe($dueDate);
});

it('não cria OS com prazo no passado', function () {
    $responsavel = tecnico();

    Livewire::actingAs(tecnico())
        ->test(CreateOrder::class)
        ->set('title', 'OS inválida')
        ->set('description', 'Descrição válida aqui.')
        ->set('assigned_to_id', $responsavel->id)
        ->set('due_date', now()->subDay()->toDateString())
        ->call('save')
        ->assertHasErrors(['due_date']);
});

it('não cria OS sem responsável', function () {
    Livewire::actingAs(tecnico())
        ->test(CreateOrder::class)
        ->set('title', 'Titulo válido')
        ->set('description', 'Descrição com mais de dez chars.')
        ->set('assigned_to_id', null)
        ->call('save')
        ->assertHasErrors(['assigned_to_id']);
});

it('colaborador não consegue criar OS pelo componente', function () {
    Livewire::actingAs(colaborador())
        ->test(CreateOrder::class)
        ->assertForbidden();
});

// ─── Fluxo de status ─────────────────────────────────────────────────────────

it('fluxo completo: pendente → em andamento → finalizada', function () {
    $responsavel = tecnico();
    $os = ordem(['assigned_to_id' => $responsavel->id, 'status' => 'pending']);

    Livewire::actingAs($responsavel)
        ->test(OrderShow::class, ['order' => $os])
        ->call('start');

    expect($os->fresh()->status)->toBe('in_progress');

    Livewire::actingAs($responsavel)
        ->test(OrderShow::class, ['order' => $os->fresh()])
        ->call('finish');

    $os->refresh();
    expect($os->status)->toBe('done')
        ->and($os->done_at)->not->toBeNull();
});

it('apenas responsável pode iniciar a OS', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $os = ordem(['requester_id' => $solicitante->id, 'assigned_to_id' => $responsavel->id, 'status' => 'pending']);

    Livewire::actingAs($solicitante)
        ->test(OrderShow::class, ['order' => $os])
        ->call('start')
        ->assertForbidden();
});

it('admin pode iniciar qualquer OS', function () {
    $os = ordem(['status' => 'pending']);

    Livewire::actingAs(admin())
        ->test(OrderShow::class, ['order' => $os])
        ->call('start');

    expect($os->fresh()->status)->toBe('in_progress');
});

// ─── Cancelar OS ─────────────────────────────────────────────────────────────

it('solicitante cancela OS pendente', function () {
    $solicitante = tecnico();
    $os = ordem(['requester_id' => $solicitante->id, 'status' => 'pending']);

    Livewire::actingAs($solicitante)
        ->test(OrderShow::class, ['order' => $os])
        ->call('cancel');

    expect($os->fresh()->status)->toBe('cancelled');
});

it('solicitante cancela OS em andamento', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $os = ordem(['requester_id' => $solicitante->id, 'assigned_to_id' => $responsavel->id, 'status' => 'in_progress']);

    Livewire::actingAs($solicitante)
        ->test(OrderShow::class, ['order' => $os])
        ->call('cancel');

    expect($os->fresh()->status)->toBe('cancelled');
});

it('admin cancela qualquer OS', function () {
    $os = ordem(['status' => 'pending']);

    Livewire::actingAs(admin())
        ->test(OrderShow::class, ['order' => $os])
        ->call('cancel');

    expect($os->fresh()->status)->toBe('cancelled');
});

it('responsável não pode cancelar OS (apenas solicitante ou admin)', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $os = ordem(['requester_id' => $solicitante->id, 'assigned_to_id' => $responsavel->id, 'status' => 'pending']);

    Livewire::actingAs($responsavel)
        ->test(OrderShow::class, ['order' => $os])
        ->call('cancel')
        ->assertForbidden();
});

it('não cancela OS finalizada', function () {
    $solicitante = tecnico();
    $os = ordem(['requester_id' => $solicitante->id, 'status' => 'done']);

    Livewire::actingAs($solicitante)
        ->test(OrderShow::class, ['order' => $os])
        ->call('cancel')
        ->assertForbidden();
});

// ─── Prazo (due_date) em OS ───────────────────────────────────────────────────

it('dueBadge retorna null quando sem prazo', function () {
    $os = ordem();
    expect($os->dueBadge())->toBeNull();
});

it('dueBadge retorna vermelho quando atrasada', function () {
    $os = ordem(['due_date' => now()->subDays(3)->toDateString(), 'status' => 'in_progress']);
    $badge = $os->dueBadge();
    expect($badge['color'])->toBe('red');
});

it('dueBadge retorna verde quando dentro do prazo', function () {
    $os = ordem(['due_date' => now()->addDays(5)->toDateString(), 'status' => 'in_progress']);
    $badge = $os->dueBadge();
    expect($badge['color'])->toBe('green');
});

it('filtro overdue exibe apenas OS atrasadas', function () {
    $eu = tecnico();
    $outro = tecnico();

    ServiceOrder::factory()->create([
        'requester_id'   => $eu->id,
        'assigned_to_id' => $outro->id,
        'title'          => 'OS-ATRASADA',
        'status'         => 'in_progress',
        'due_date'       => now()->subDay()->toDateString(),
    ]);

    ServiceOrder::factory()->create([
        'requester_id'   => $eu->id,
        'assigned_to_id' => $outro->id,
        'title'          => 'OS-NO-PRAZO',
        'status'         => 'in_progress',
        'due_date'       => now()->addDays(5)->toDateString(),
    ]);

    Livewire::actingAs($eu)
        ->test(OrderList::class)
        ->set('status', 'overdue')
        ->assertSee('OS-ATRASADA')
        ->assertDontSee('OS-NO-PRAZO');
});

// ─── Transferência de responsabilidade ───────────────────────────────────────

it('responsável solicita transferência para outro técnico', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $novo        = tecnico();
    $os = ordem(['requester_id' => $solicitante->id, 'assigned_to_id' => $responsavel->id, 'status' => 'in_progress']);

    Livewire::actingAs($responsavel)
        ->test(OrderShow::class, ['order' => $os])
        ->set('transferTo', $novo->id)
        ->set('transferNote', 'Estarei de férias.')
        ->call('requestTransfer')
        ->assertHasNoErrors();

    $os->refresh();
    expect($os->transfer_requested_to_id)->toBe($novo->id)
        ->and($os->transfer_note)->toBe('Estarei de férias.');
});

it('solicitante não pode pedir transferência (só o responsável)', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $novo        = tecnico();
    $os = ordem(['requester_id' => $solicitante->id, 'assigned_to_id' => $responsavel->id, 'status' => 'in_progress']);

    Livewire::actingAs($solicitante)
        ->test(OrderShow::class, ['order' => $os])
        ->set('transferTo', $novo->id)
        ->call('requestTransfer')
        ->assertForbidden();
});

it('admin aprova transferência e responsável é atualizado', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $novo        = tecnico();
    $os = ordem([
        'requester_id'             => $solicitante->id,
        'assigned_to_id'           => $responsavel->id,
        'status'                   => 'in_progress',
        'transfer_requested_to_id' => $novo->id,
    ]);

    Livewire::actingAs(admin())
        ->test(OrderShow::class, ['order' => $os])
        ->call('approveTransfer');

    $os->refresh();
    expect($os->assigned_to_id)->toBe($novo->id)
        ->and($os->transfer_requested_to_id)->toBeNull();
});

it('admin rejeita transferência e campos são limpos', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $novo        = tecnico();
    $os = ordem([
        'requester_id'             => $solicitante->id,
        'assigned_to_id'           => $responsavel->id,
        'status'                   => 'in_progress',
        'transfer_requested_to_id' => $novo->id,
    ]);

    Livewire::actingAs(admin())
        ->test(OrderShow::class, ['order' => $os])
        ->call('rejectTransfer');

    $os->refresh();
    expect($os->assigned_to_id)->toBe($responsavel->id)
        ->and($os->transfer_requested_to_id)->toBeNull();
});

it('técnico não-admin não pode aprovar transferência', function () {
    $solicitante = tecnico();
    $responsavel = tecnico();
    $novo        = tecnico();
    $os = ordem([
        'requester_id'             => $solicitante->id,
        'assigned_to_id'           => $responsavel->id,
        'status'                   => 'in_progress',
        'transfer_requested_to_id' => $novo->id,
    ]);

    Livewire::actingAs($solicitante)
        ->test(OrderShow::class, ['order' => $os])
        ->call('approveTransfer')
        ->assertForbidden();
});

// ─── Comentários em OS ───────────────────────────────────────────────────────

it('solicitante adiciona comentário na sua OS', function () {
    $solicitante = tecnico();
    $os = ordem(['requester_id' => $solicitante->id, 'status' => 'in_progress']);

    Livewire::actingAs($solicitante)
        ->test(OrderShow::class, ['order' => $os])
        ->set('commentBody', 'Atualizando o andamento da tarefa.')
        ->call('addComment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('service_order_comments', [
        'service_order_id' => $os->id,
        'user_id'          => $solicitante->id,
    ]);
});

it('terceiro técnico não consegue visualizar OS alheia', function () {
    $os      = ordem(['status' => 'in_progress']);
    $intruso = tecnico();

    Livewire::actingAs($intruso)
        ->test(OrderShow::class, ['order' => $os])
        ->assertForbidden();
});
