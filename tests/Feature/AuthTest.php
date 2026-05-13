<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

// ─── Páginas públicas ───────────────────────────────────────────────────────

it('exibe a página de login', function () {
    $this->get(route('login'))->assertOk();
});

it('exibe a página de registro', function () {
    $this->get(route('register'))->assertOk();
});

it('redireciona usuário autenticado para fora do login', function () {
    $this->actingAs(colaborador())
        ->get(route('login'))
        ->assertRedirect();
});

// ─── Login ──────────────────────────────────────────────────────────────────

it('loga com credenciais válidas', function () {
    $user = colaborador(['email' => 'user@test.com', 'password' => bcrypt('senha123')]);

    Livewire::test(Login::class)
        ->set('email', 'user@test.com')
        ->set('password', 'senha123')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejeita credenciais inválidas', function () {
    colaborador(['email' => 'user@test.com', 'password' => bcrypt('correta')]);

    Livewire::test(Login::class)
        ->set('email', 'user@test.com')
        ->set('password', 'errada')
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

it('valida campos obrigatórios no login', function () {
    Livewire::test(Login::class)
        ->set('email', '')
        ->set('password', '')
        ->call('login')
        ->assertHasErrors(['email', 'password']);
});

it('valida formato de e-mail no login', function () {
    Livewire::test(Login::class)
        ->set('email', 'nao-e-email')
        ->set('password', 'qualquer')
        ->call('login')
        ->assertHasErrors(['email']);
});

// ─── Registro ───────────────────────────────────────────────────────────────

it('registra novo colaborador com dados válidos', function () {
    Livewire::test(Register::class)
        ->set('name', 'João Silva')
        ->set('email', 'joao@test.com')
        ->set('password', 'senha1234')
        ->set('password_confirmation', 'senha1234')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', [
        'email' => 'joao@test.com',
        'role'  => 'collaborator',
    ]);
});

it('não registra com e-mail duplicado', function () {
    colaborador(['email' => 'existente@test.com']);

    Livewire::test(Register::class)
        ->set('name', 'Outro')
        ->set('email', 'existente@test.com')
        ->set('password', 'senha1234')
        ->set('password_confirmation', 'senha1234')
        ->call('register')
        ->assertHasErrors(['email']);
});

it('não registra com senhas diferentes', function () {
    Livewire::test(Register::class)
        ->set('name', 'Teste')
        ->set('email', 'novo@test.com')
        ->set('password', 'senha1234')
        ->set('password_confirmation', 'diferente')
        ->call('register')
        ->assertHasErrors(['password']);
});

it('não registra com senha curta', function () {
    Livewire::test(Register::class)
        ->set('name', 'Teste')
        ->set('email', 'novo@test.com')
        ->set('password', '123')
        ->set('password_confirmation', '123')
        ->call('register')
        ->assertHasErrors(['password']);
});

// ─── Logout ─────────────────────────────────────────────────────────────────

it('desloga o usuário', function () {
    $this->actingAs(colaborador())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
