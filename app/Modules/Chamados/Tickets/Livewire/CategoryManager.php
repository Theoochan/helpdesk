<?php

declare(strict_types=1);

namespace App\Modules\Chamados\Tickets\Livewire;

use App\Modules\Chamados\Tickets\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CategoryManager extends Component
{
    public string $search = '';

    public bool   $showForm     = false;
    public string $categoryName  = '';
    public string $categoryColor = '#6b7280';

    public ?int   $editCategoryId    = null;
    public string $editCategoryName  = '';
    public string $editCategoryColor = '';

    public ?int   $confirmDeleteId = null;

    public function mount(): void
    {
        $this->authorize('manage-technicians');
    }

    // ─── Criação ─────────────────────────────────────────────────────────────

    public function openForm(): void
    {
        $this->categoryName  = '';
        $this->categoryColor = '#6b7280';
        $this->showForm      = true;
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
        $this->showForm     = false;
    }

    // ─── Edição ───────────────────────────────────────────────────────────────

    public function startEdit(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editCategoryId    = $id;
        $this->editCategoryName  = $cat->name;
        $this->editCategoryColor = $cat->color ?? '#6b7280';
    }

    public function saveEdit(): void
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

    public function cancelEdit(): void
    {
        $this->editCategoryId = null;
        $this->resetValidation();
    }

    // ─── Exclusão ────────────────────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function deleteConfirmed(): void
    {
        Category::findOrFail($this->confirmDeleteId)->delete();
        session()->flash('success', 'Categoria removida.');
        $this->confirmDeleteId = null;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        $categories = Category::withCount('tickets')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.category-manager', compact('categories'));
    }
}
