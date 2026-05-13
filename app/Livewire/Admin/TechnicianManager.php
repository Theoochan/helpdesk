<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class TechnicianManager extends Component
{
    // ─── Formulário de criação ───────────────────────────────────────────────
    public bool $showForm = false;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required|min:8')]
    public string $password = '';

    #[Validate('required|in:technician,admin')]
    public string $role = 'technician';

    // ─── Confirmação de exclusão ─────────────────────────────────────────────
    public ?int $confirmDeleteId = null;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('manage-technicians');
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
            'role'     => $this->role,
        ]);

        session()->flash('success', 'Usuário criado com sucesso!');
        $this->resetForm();
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void
    {
        // Impede auto-exclusão
        abort_if($id === auth()->id(), 403);
        $this->confirmDeleteId = $id;
    }

    public function deleteTechnician(): void
    {
        $user = User::findOrFail($this->confirmDeleteId);
        abort_if($user->id === auth()->id(), 403);

        $user->delete();
        $this->confirmDeleteId = null;
        session()->flash('success', 'Usuário removido.');
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    private function resetForm(): void
    {
        $this->name     = '';
        $this->email    = '';
        $this->password = '';
        $this->role     = 'technician';
        $this->resetValidation();
    }

    public function render()
    {
        $technicians = User::whereIn('role', ['technician', 'admin'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.technician-manager', compact('technicians'));
    }
}
