<?php

use App\Modules\Core\Livewire\Auth\Login;
use App\Modules\Core\Models\User;
use Livewire\Livewire;

// ─── Páginas públicas ───────────────────────────────────────────────────────

it('exibe a página de login', function () {
    $this->get(route('login'))->assertOk();
});

it('rota de registro não existe', function () {
    $this->get('/register')->assertNotFound();
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

// ─── Logout ─────────────────────────────────────────────────────────────────

it('desloga o usuário', function () {
    $this->actingAs(colaborador())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
