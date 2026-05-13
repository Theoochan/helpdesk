<?php

use App\Livewire\Admin\TechnicianManager;
use App\Models\User;
use Livewire\Livewire;

// ─── ACL: apenas admin acessa ────────────────────────────────────────────────

it('colaborador não acessa área admin', function () {
    $this->actingAs(colaborador())
        ->get(route('admin.technicians'))
        ->assertForbidden();
});

it('técnico comum não acessa área admin', function () {
    $this->actingAs(tecnico())
        ->get(route('admin.technicians'))
        ->assertForbidden();
});

it('admin acessa área de gestão de técnicos', function () {
    $this->actingAs(admin())
        ->get(route('admin.technicians'))
        ->assertOk();
});

it('colaborador não acessa o componente diretamente', function () {
    Livewire::actingAs(colaborador())
        ->test(TechnicianManager::class)
        ->assertForbidden();
});

// ─── CRUD de técnicos ────────────────────────────────────────────────────────

it('admin cadastra novo técnico', function () {
    Livewire::actingAs(admin())
        ->test(TechnicianManager::class)
        ->set('showForm', true)
        ->set('name', 'Novo Técnico')
        ->set('email', 'novo@helpdesk.com')
        ->set('password', 'senha1234')
        ->set('role', 'technician')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'novo@helpdesk.com',
        'role'  => 'technician',
    ]);
});

it('admin cadastra novo admin', function () {
    Livewire::actingAs(admin())
        ->test(TechnicianManager::class)
        ->set('showForm', true)
        ->set('name', 'Novo Admin')
        ->set('email', 'novoadmin@helpdesk.com')
        ->set('password', 'senha1234')
        ->set('role', 'admin')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'novoadmin@helpdesk.com',
        'role'  => 'admin',
    ]);
});

it('não cadastra com e-mail já existente', function () {
    tecnico(['email' => 'existente@test.com']);

    Livewire::actingAs(admin())
        ->test(TechnicianManager::class)
        ->set('showForm', true)
        ->set('name', 'Outro')
        ->set('email', 'existente@test.com')
        ->set('password', 'senha1234')
        ->set('role', 'technician')
        ->call('save')
        ->assertHasErrors(['email']);
});

it('não aceita role inválida no cadastro', function () {
    Livewire::actingAs(admin())
        ->test(TechnicianManager::class)
        ->set('showForm', true)
        ->set('name', 'Teste')
        ->set('email', 'teste@test.com')
        ->set('password', 'senha1234')
        ->set('role', 'collaborator') // colaborador não é permitido aqui
        ->call('save')
        ->assertHasErrors(['role']);
});

it('admin remove técnico', function () {
    $tec = tecnico(['email' => 'pararemover@test.com']);

    Livewire::actingAs(admin())
        ->test(TechnicianManager::class)
        ->call('confirmDelete', $tec->id)
        ->call('deleteTechnician');

    $this->assertDatabaseMissing('users', ['id' => $tec->id]);
});

it('admin não consegue se auto-excluir', function () {
    $adm = admin();

    Livewire::actingAs($adm)
        ->test(TechnicianManager::class)
        ->call('confirmDelete', $adm->id);

    // confirmDelete deve abortar com 403
    $this->assertDatabaseHas('users', ['id' => $adm->id]);
});

it('busca filtra técnicos pelo nome', function () {
    tecnico(['name' => 'Carlos Ferreira']);
    tecnico(['name' => 'João Silva']);

    Livewire::actingAs(admin())
        ->test(TechnicianManager::class)
        ->set('search', 'Carlos')
        ->assertSee('Carlos Ferreira')
        ->assertDontSee('João Silva');
});

// ─── Menu de navegação ───────────────────────────────────────────────────────

it('menu de Administração aparece apenas para admin', function () {
    $this->actingAs(admin())
        ->get(route('technician.dashboard'))
        ->assertSee('Administração');
});

it('menu de Administração não aparece para técnico comum', function () {
    $this->actingAs(tecnico())
        ->get(route('technician.dashboard'))
        ->assertDontSee('Administração');
});
