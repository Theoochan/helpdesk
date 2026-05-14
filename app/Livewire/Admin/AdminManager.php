<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class AdminManager extends Component
{
    // ─── Aba ativa ──────────────────────────────────────────────────────────
    #[Url]
    public string $tab = 'technicians'; // technicians | collaborators | categories

    // ─── Formulário de usuário (técnicos e colaboradores) ───────────────────
    public bool   $showForm = false;
    public string $name     = '';
    public string $email    = '';
    public string $password = '';
    public string $role     = 'technician';

    // ─── Edição de papel (inline) ────────────────────────────────────────────
    public ?int   $editRoleId   = null;
    public string $editRoleValue = '';

    // ─── Formulário de categoria ─────────────────────────────────────────────
    public bool   $showCategoryForm  = false;
    public string $categoryName      = '';
    public string $categoryColor     = '#6b7280';

    // ─── Edição de categoria (inline) ────────────────────────────────────────
    public ?int   $editCategoryId    = null;
    public string $editCategoryName  = '';
    public string $editCategoryColor = '';

    // ─── Busca e confirmação ─────────────────────────────────────────────────
    public string $search           = '';
    public ?int   $confirmDeleteId  = null;
    public string $confirmDeleteType = ''; // 'user' | 'category'

    public function mount(): void
    {
        $this->authorize('manage-technicians');
    }

    // ─── Troca de aba ────────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        $this->tab      = $tab;
        $this->search   = '';
        $this->showForm = false;
        $this->showCategoryForm = false;
        $this->cancelEdit();
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

    // ─── Exclusão de usuário ─────────────────────────────────────────────────

    public function confirmDelete(int $id, string $type = 'user'): void
    {
        abort_if($type === 'user' && $id === auth()->id(), 403);
        $this->confirmDeleteId   = $id;
        $this->confirmDeleteType = $type;
    }

    public function deleteConfirmed(): void
    {
        if ($this->confirmDeleteType === 'category') {
            Category::findOrFail($this->confirmDeleteId)->delete();
            session()->flash('success', 'Categoria removida.');
        } else {
            $user = User::findOrFail($this->confirmDeleteId);
            abort_if($user->id === auth()->id(), 403);
            $user->delete();
            session()->flash('success', 'Usuário removido.');
        }

        $this->confirmDeleteId   = null;
        $this->confirmDeleteType = '';
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId   = null;
        $this->confirmDeleteType = '';
    }

    // ─── Categorias ──────────────────────────────────────────────────────────

    public function openCategoryForm(): void
    {
        $this->categoryName  = '';
        $this->categoryColor = '#6b7280';
        $this->showCategoryForm = true;
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName'  => 'required|string|max:60|unique:categories,name',
            'categoryColor' => 'required|string|max:20',
        ]);

        Category::create(['name' => $this->categoryName, 'color' => $this->categoryColor]);

        session()->flash('success', 'Categoria criada.');
        $this->categoryName = '';
        $this->showCategoryForm = false;
    }

    public function startEditCategory(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editCategoryId    = $id;
        $this->editCategoryName  = $cat->name;
        $this->editCategoryColor = $cat->color ?? '#6b7280';
    }

    public function saveEditCategory(): void
    {
        $this->validate([
            'editCategoryName'  => "required|string|max:60|unique:categories,name,{$this->editCategoryId}",
            'editCategoryColor' => 'required|string|max:20',
        ]);

        Category::findOrFail($this->editCategoryId)->update([
            'name'  => $this->editCategoryName,
            'color' => $this->editCategoryColor,
        ]);

        session()->flash('success', 'Categoria atualizada.');
        $this->editCategoryId = null;
    }

    public function cancelEditCategory(): void
    {
        $this->editCategoryId = null;
        $this->resetValidation();
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

        $categories = Category::withCount('tickets')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')->get();

        return view('livewire.admin.admin-manager', compact('technicians', 'collaborators', 'categories'));
    }
}
