<?php

use App\Modules\Core\Livewire\Admin\AdminManager;
use App\Modules\Core\Livewire\UserProfile;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

// ─── Modal de nome ───────────────────────────────────────────────────────────

it('usuário atualiza o próprio nome com sucesso', function () {
    $user = colaborador(['name' => 'Nome Antigo']);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'name')
        ->set('newName', 'Nome Novo')
        ->call('saveName')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Nome Novo');
});

it('técnico atualiza o próprio nome', function () {
    $user = tecnico(['name' => 'Técnico Antigo']);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'name')
        ->set('newName', 'Técnico Novo')
        ->call('saveName')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Técnico Novo');
});

it('rejeita nome vazio', function () {
    Livewire::actingAs(colaborador())
        ->test(UserProfile::class)
        ->call('openModal', 'name')
        ->set('newName', '')
        ->call('saveName')
        ->assertHasErrors(['newName']);
});

it('rejeita nome com menos de 2 caracteres', function () {
    Livewire::actingAs(colaborador())
        ->test(UserProfile::class)
        ->call('openModal', 'name')
        ->set('newName', 'A')
        ->call('saveName')
        ->assertHasErrors(['newName']);
});

it('nome com exatamente 2 caracteres é aceito', function () {
    $user = colaborador();

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'name')
        ->set('newName', 'Li')
        ->call('saveName')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Li');
});

// ─── Modal de senha ──────────────────────────────────────────────────────────

it('usuário altera a própria senha com sucesso', function () {
    $user = colaborador(['password' => Hash::make('senha-atual-123')]);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'password')
        ->set('currentPassword', 'senha-atual-123')
        ->set('newPassword', 'nova-senha-456')
        ->set('newPasswordConfirmation', 'nova-senha-456')
        ->call('savePassword')
        ->assertHasNoErrors();

    expect(Hash::check('nova-senha-456', $user->fresh()->password))->toBeTrue();
});

it('rejeita senha atual incorreta', function () {
    $user = colaborador(['password' => Hash::make('correta123')]);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'password')
        ->set('currentPassword', 'errada123')
        ->set('newPassword', 'nova-senha-456')
        ->set('newPasswordConfirmation', 'nova-senha-456')
        ->call('savePassword')
        ->assertHasErrors(['currentPassword']);

    // senha não foi alterada
    expect(Hash::check('correta123', $user->fresh()->password))->toBeTrue();
});

it('rejeita nova senha com menos de 8 caracteres', function () {
    $user = colaborador(['password' => Hash::make('atual1234')]);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'password')
        ->set('currentPassword', 'atual1234')
        ->set('newPassword', 'curta')
        ->set('newPasswordConfirmation', 'curta')
        ->call('savePassword')
        ->assertHasErrors(['newPassword']);
});

it('rejeita confirmação diferente da nova senha', function () {
    $user = colaborador(['password' => Hash::make('atual1234')]);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'password')
        ->set('currentPassword', 'atual1234')
        ->set('newPassword', 'nova-senha-ok')
        ->set('newPasswordConfirmation', 'confirmacao-diferente')
        ->call('savePassword')
        ->assertHasErrors(['newPasswordConfirmation']);
});

it('rejeita todos os campos de senha vazios', function () {
    Livewire::actingAs(colaborador())
        ->test(UserProfile::class)
        ->call('openModal', 'password')
        ->set('currentPassword', '')
        ->set('newPassword', '')
        ->set('newPasswordConfirmation', '')
        ->call('savePassword')
        ->assertHasErrors(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
});

it('rejeita confirmação de senha vazia', function () {
    $user = colaborador(['password' => Hash::make('atual1234')]);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'password')
        ->set('currentPassword', 'atual1234')
        ->set('newPassword', 'nova-senha-ok')
        ->set('newPasswordConfirmation', '')
        ->call('savePassword')
        ->assertHasErrors(['newPasswordConfirmation']);
});

it('fecha o modal e limpa os campos após salvar a senha', function () {
    $user = colaborador(['password' => Hash::make('atual1234')]);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->call('openModal', 'password')
        ->set('currentPassword', 'atual1234')
        ->set('newPassword', 'nova-senha-ok')
        ->set('newPasswordConfirmation', 'nova-senha-ok')
        ->call('savePassword')
        ->assertSet('modal', null)
        ->assertSet('currentPassword', '')
        ->assertSet('newPassword', '')
        ->assertSet('newPasswordConfirmation', '');
});

// ─── Admin editando outro usuário ────────────────────────────────────────────

it('admin edita nome e email de outro usuário', function () {
    $alvo = tecnico(['name' => 'Nome Antigo', 'email' => 'antigo@test.com']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditUser', $alvo->id)
        ->set('editUserName', 'Nome Editado')
        ->set('editUserEmail', 'novo@test.com')
        ->set('editUserPassword', '')
        ->call('saveEditUser')
        ->assertHasNoErrors();

    $atualizado = $alvo->fresh();
    expect($atualizado->name)->toBe('Nome Editado');
    expect($atualizado->email)->toBe('novo@test.com');
});

it('admin redefine senha de outro usuário sem informar senha atual', function () {
    $alvo = tecnico(['password' => Hash::make('senha-antiga')]);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditUser', $alvo->id)
        ->set('editUserName', $alvo->name)
        ->set('editUserEmail', $alvo->email)
        ->set('editUserPassword', 'nova-senha-admin')
        ->call('saveEditUser')
        ->assertHasNoErrors();

    expect(Hash::check('nova-senha-admin', $alvo->fresh()->password))->toBeTrue();
});

it('senha vazia no admin mantém a senha atual do usuário', function () {
    $alvo = tecnico(['password' => Hash::make('senha-original')]);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditUser', $alvo->id)
        ->set('editUserName', $alvo->name)
        ->set('editUserEmail', $alvo->email)
        ->set('editUserPassword', '')
        ->call('saveEditUser')
        ->assertHasNoErrors();

    expect(Hash::check('senha-original', $alvo->fresh()->password))->toBeTrue();
});

it('admin não pode usar email já existente de outro usuário', function () {
    tecnico(['email' => 'ocupado@test.com']);
    $alvo = tecnico(['email' => 'alvo@test.com']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditUser', $alvo->id)
        ->set('editUserName', $alvo->name)
        ->set('editUserEmail', 'ocupado@test.com')
        ->call('saveEditUser')
        ->assertHasErrors(['editUserEmail']);
});

it('admin pode salvar sem alterar o próprio email do usuário alvo', function () {
    $alvo = tecnico(['email' => 'mesmo@test.com']);

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditUser', $alvo->id)
        ->set('editUserName', 'Nome Atualizado')
        ->set('editUserEmail', 'mesmo@test.com') // mesmo email
        ->set('editUserPassword', '')
        ->call('saveEditUser')
        ->assertHasNoErrors();

    expect($alvo->fresh()->name)->toBe('Nome Atualizado');
});

it('admin não aceita nova senha com menos de 8 caracteres', function () {
    $alvo = tecnico();

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditUser', $alvo->id)
        ->set('editUserName', $alvo->name)
        ->set('editUserEmail', $alvo->email)
        ->set('editUserPassword', 'curta')
        ->call('saveEditUser')
        ->assertHasErrors(['editUserPassword']);
});

it('admin não aceita nome vazio ao editar usuário', function () {
    $alvo = tecnico();

    Livewire::actingAs(admin())
        ->test(AdminManager::class)
        ->call('startEditUser', $alvo->id)
        ->set('editUserName', '')
        ->set('editUserEmail', $alvo->email)
        ->call('saveEditUser')
        ->assertHasErrors(['editUserName']);
});
