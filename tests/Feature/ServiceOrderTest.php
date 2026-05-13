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

it('técnico vê apenas suas OS (como solicitante ou responsável)', function () {
    $eu     = tecnico();
    $outro  = tecnico();
    $terceiro = tecnico();

    // OS que envolve "eu"
    $minhaOs = ServiceOrder::factory()->create([
        'requester_id'   => $eu->id,
        'assigned_to_id' => $outro->id,
        'title'          => 'OS-MINHA-XYZ',
    ]);

    // OS que NÃO envolve "eu"
    ServiceOrder::factory()->create([
        'requester_id'   => $outro->id,
        'assigned_to_id' => $terceiro->id,
        'title'          => 'OS-ALHEIA-XYZ',
    ]);

    Livewire::actingAs($eu)
        ->test(OrderList::class)
        ->assertSee('OS-MINHA-XYZ')
        ->assertDontSee('OS-ALHEIA-XYZ');
});

it('admin vê todas as OS independente de participação', function () {
    $t1 = tecnico();
    $t2 = tecnico();
    ServiceOrder::factory()->create(['requester_id' => $t1->id, 'assigned_to_id' => $t2->id, 'title' => 'OS-A']);
    ServiceOrder::factory()->create(['requester_id' => $t2->id, 'assigned_to_id' => $t1->id, 'title' => 'OS-B']);

    Livewire::actingAs(admin())
        ->test(OrderList::class)
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

it('não cria OS sem responsável', function () {
    Livewire::actingAs(tecnico())
        ->test(CreateOrder::class)
        ->set('title', 'Titulo válido')
        ->set('description', 'Descrição com mais de dez chars.')
        ->set('assigned_to_id', null)
        ->call('save')
        ->assertHasErrors(['assigned_to_id']);
});

it('não cria OS com título vazio', function () {
    Livewire::actingAs(tecnico())
        ->test(CreateOrder::class)
        ->set('title', '')
        ->set('description', 'Descrição válida.')
        ->set('assigned_to_id', tecnico()->id)
        ->call('save')
        ->assertHasErrors(['title']);
});

it('colaborador não consegue criar OS pelo componente', function () {
    Livewire::actingAs(colaborador())
        ->test(CreateOrder::class)
        ->assertForbidden();
});

// ─── Fluxo de status: pending → in_progress → done ──────────────────────────

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
    // $solicitante pode VER a OS (é participante) mas NÃO pode iniciá-la
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

it('terceiro técnico não consegue nem visualizar OS alheia', function () {
    $os      = ordem(['status' => 'in_progress']);
    $intruso = tecnico();

    // A policy de view e comment são idênticas: só participantes acessam.
    // O mount() nega acesso antes de qualquer ação.
    Livewire::actingAs($intruso)
        ->test(OrderShow::class, ['order' => $os])
        ->assertForbidden();
});
