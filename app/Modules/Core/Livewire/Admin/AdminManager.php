<?php

declare(strict_types=1);

namespace App\Modules\Core\Livewire\Admin;

use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class AdminManager extends Component
{
    // ─── Aba ativa ──────────────────────────────────────────────────────────
    #[Url]
    public string $tab = 'technicians'; // technicians | collaborators

    // ─── Formulário de usuário ───────────────────────────────────────────────
    public bool   $showForm = false;
    public string $name     = '';
    public string $email    = '';
    public string $password = '';
    public string $role     = 'technician';

    // ─── Edição de papel (inline) ────────────────────────────────────────────
    public ?int   $editRoleId    = null;
    public string $editRoleValue = '';

    // ─── Edição de usuário (inline) ──────────────────────────────────────────
    public ?int   $editUserId       = null;
    public string $editUserName     = '';
    public string $editUserEmail    = '';
    public string $editUserPassword = '';

    // ─── Busca e confirmação ─────────────────────────────────────────────────
    public string $search          = '';
    public ?int   $confirmDeleteId = null;

    public function mount(): void
    {
        $this->authorize('manage-technicians');
    }

    // ─── Troca de aba ────────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        $this->tab    = $tab;
        $this->search = '';
        $this->showForm = false;
        $this->cancelEdit();
        $this->cancelEditUser();
        $this->confirmDeleteId = null;
    }

    // ─── Formulário de usuário ────────────────────────────────────────────────

    public function openForm(): void
    {
        $this->resetUserForm();
        $this->role     = $this->tab === 'collaborators' ? 'collaborator' : 'technician';
        $this->showForm = true;
    }

    public function saveUser(): void
    {
        $allowedRoles = $this->tab === 'collaborators'
            ? 'in:collaborator,technician,admin'
            : 'in:technician,admin';

        $this->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => "required|{$allowedRoles}",
        ]);

        User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
            'role'     => $this->role,
        ]);

        session()->flash('success', 'Usuário criado com sucesso!');
        $this->resetUserForm();
        $this->showForm = false;
    }

    // ─── Edição de papel ─────────────────────────────────────────────────────

    public function startEditRole(int $userId): void
    {
        abort_if($userId === auth()->id(), 403);
        $user = User::findOrFail($userId);
        $this->editRoleId    = $userId;
        $this->editRoleValue = $user->role;
    }

    public function saveRole(): void
    {
        abort_if($this->editRoleId === auth()->id(), 403);

        $this->validate(['editRoleValue' => 'required|in:collaborator,technician,admin']);

        User::findOrFail($this->editRoleId)->update(['role' => $this->editRoleValue]);

        session()->flash('success', 'Papel atualizado.');
        $this->editRoleId = null;
    }

    public function cancelEdit(): void
    {
        $this->editRoleId    = null;
        $this->editRoleValue = '';
    }

    // ─── Edição de usuário ───────────────────────────────────────────────────

    public function startEditUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editUserId       = $userId;
        $this->editUserName     = $user->name;
        $this->editUserEmail    = $user->email;
        $this->editUserPassword = '';
        $this->editRoleId       = null;
    }

    public function saveEditUser(): void
    {
        $this->validate([
            'editUserName'     => 'required|string|max:100',
            'editUserEmail'    => "required|email|unique:users,email,{$this->editUserId}",
            'editUserPassword' => 'nullable|min:8',
        ]);

        $data = [
            'name'  => $this->editUserName,
            'email' => $this->editUserEmail,
        ];

        if (filled($this->editUserPassword)) {
            $data['password'] = Hash::make($this->editUserPassword);
        }

        User::findOrFail($this->editUserId)->update($data);

        session()->flash('success', 'Usuário atualizado.');
        $this->cancelEditUser();
    }

    public function cancelEditUser(): void
    {
        $this->editUserId       = null;
        $this->editUserName     = '';
        $this->editUserEmail    = '';
        $this->editUserPassword = '';
        $this->resetValidation();
    }

    // ─── Exclusão de usuário ─────────────────────────────────────────────────

    public function confirmDelete(int $id, string $type = 'user'): void
    {
        abort_if($id === auth()->id(), 403);
        $this->confirmDeleteId = $id;
    }

    public function deleteConfirmed(): void
    {
        $user = User::findOrFail($this->confirmDeleteId);
        abort_if($user->id === auth()->id(), 403);
        $user->delete();

        session()->flash('success', 'Usuário removido.');
        $this->confirmDeleteId = null;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    // ─── Reset ───────────────────────────────────────────────────────────────

    private function resetUserForm(): void
    {
        $this->name     = '';
        $this->email    = '';
        $this->password = '';
        $this->role     = 'technician';
        $this->resetValidation();
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        $technicians = User::whereIn('role', ['technician', 'admin'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')->get();

        $collaborators = User::where('role', 'collaborator')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')->get();

        return view('livewire.admin.admin-manager', compact('technicians', 'collaborators'));
    }
}
