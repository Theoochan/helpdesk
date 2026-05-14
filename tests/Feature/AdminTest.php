<?php

use App\Livewire\Admin\AdminManager;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

// ─── ACL: apenas admin acessa ────────────────────────────────────────────────

it('colaborador não acessa área admin', function () {
    $this->actingAs(colaborador())
        ->get(route('admin.index'))
        ->assertForbidden();
});

it('técnico comum não acessa área admin', function () {
    $this->actingAs(tecnico())
        ->get(route('admin.index'))
        ->assertForbidden();
});

it('admin acessa área de gestão', function () {
    $this->actingAs(admin())
        ->get(route('admin.index'))
        ->assertOk();
});

it('colaborador não acessa o componente diretamente', function () {
    Livewire::actingAs(colaborador())
        ->test(AdminManager::class)
        ->assertForbidden();
});

// ─── CRUD de técnicos ────────────────────────────────────────────────────────

it('admin cadastra novo técnico', function () {
    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->set('tab', 'technicians')
        ->set('showForm', true)
        ->set('name', 'Novo Técnico')
        ->set('email', 'novo@helpdesk.com')
        ->set('password', 'senha1234')
        ->set('role', 'technician')
        ->call('saveUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'novo@helpdesk.com',
        'role'  => 'technician',
    ]);
});

it('admin cadastra novo admin', function () {
    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->set('tab', 'technicians')
        ->set('showForm', true)
        ->set('name', 'Novo Admin')
        ->set('email', 'novoadmin@helpdesk.com')
        ->set('password', 'senha1234')
        ->set('role', 'admin')
        ->call('saveUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'novoadmin@helpdesk.com',
        'role'  => 'admin',
    ]);
});

it('não cadastra com e-mail já existente', function () {
    tecnico(['email' => 'existente@test.com']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->set('tab', 'technicians')
        ->set('showForm', true)
        ->set('name', 'Outro')
        ->set('email', 'existente@test.com')
        ->set('password', 'senha1234')
        ->set('role', 'technician')
        ->call('saveUser')
        ->assertHasErrors(['email']);
});

it('não aceita role inválida na aba técnicos', function () {
    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->set('tab', 'technicians')
        ->set('showForm', true)
        ->set('name', 'Teste')
        ->set('email', 'teste@test.com')
        ->set('password', 'senha1234')
        ->set('role', 'collaborator') // colaborador não permitido na aba técnicos
        ->call('saveUser')
        ->assertHasErrors(['role']);
});

it('admin remove técnico', function () {
    $tec = tecnico(['email' => 'pararemover@test.com']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('confirmDelete', $tec->id, 'user')
        ->call('deleteConfirmed');

    $this->assertDatabaseMissing('users', ['id' => $tec->id]);
});

it('admin não consegue se auto-excluir', function () {
    $adm = admin();

    Livewire::actingAs($adm)
        ->test(AdminManager::class)
        ->call('confirmDelete', $adm->id, 'user')
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $adm->id]);
});

it('busca filtra técnicos pelo nome', function () {
    tecnico(['name' => 'Carlos Ferreira']);
    tecnico(['name' => 'João Silva']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->set('tab', 'technicians')
        ->set('search', 'Carlos')
        ->assertSee('Carlos Ferreira')
        ->assertDontSee('João Silva');
});

// ─── CRUD de colaboradores ───────────────────────────────────────────────────

it('admin cadastra novo colaborador', function () {
    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->set('tab', 'collaborators')
        ->set('showForm', true)
        ->set('name', 'Maria Silva')
        ->set('email', 'maria@empresa.com')
        ->set('password', 'senha1234')
        ->set('role', 'collaborator')
        ->call('saveUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'maria@empresa.com',
        'role'  => 'collaborator',
    ]);
});

it('admin promove colaborador a técnico', function () {
    $collab = colaborador();

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditRole', $collab->id)
        ->set('editRoleValue', 'technician')
        ->call('saveRole')
        ->assertHasNoErrors();

    expect($collab->fresh()->role)->toBe('technician');
});

it('admin remove colaborador', function () {
    $collab = colaborador(['email' => 'removecollab@test.com']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('confirmDelete', $collab->id, 'user')
        ->call('deleteConfirmed');

    $this->assertDatabaseMissing('users', ['id' => $collab->id]);
});

// ─── CRUD de categorias ──────────────────────────────────────────────────────

it('admin cria categoria', function () {
    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->set('tab', 'categories')
        ->set('categoryName', 'Infraestrutura')
        ->set('categoryColor', '#3b82f6')
        ->call('saveCategory')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', ['name' => 'Infraestrutura']);
});

it('não cria categoria com nome duplicado', function () {
    Category::create(['name' => 'Rede', 'color' => '#999']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->set('tab', 'categories')
        ->set('categoryName', 'Rede')
        ->set('categoryColor', '#3b82f6')
        ->call('saveCategory')
        ->assertHasErrors(['categoryName']);
});

it('admin edita categoria', function () {
    $cat = Category::create(['name' => 'Velha', 'color' => '#aaa']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditCategory', $cat->id)
        ->set('editCategoryName', 'Nova')
        ->call('saveEditCategory')
        ->assertHasNoErrors();

    expect($cat->fresh()->name)->toBe('Nova');
});

it('admin remove categoria', function () {
    $cat = Category::create(['name' => 'Temporária', 'color' => '#aaa']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('confirmDelete', $cat->id, 'category')
        ->call('deleteConfirmed');

    $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
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
