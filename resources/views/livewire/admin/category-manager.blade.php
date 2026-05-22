<div>

    {{-- Modal de confirmação de exclusão --}}
    @if($confirmDeleteId)
        <div class="modal modal-open" x-data x-transition>
            <div class="modal-box max-w-sm">
                <h3 class="font-bold text-lg mb-2">Confirmar exclusão</h3>
                <p class="text-sm text-base-content/70 mb-5">
                    Tem certeza que deseja remover esta categoria? Esta ação não pode ser desfeita.
                </p>
                <div class="modal-action">
                    <button wire:click="cancelDelete" class="btn btn-ghost btn-sm">
                        Cancelar
                    </button>
                    <button wire:click="deleteConfirmed" class="btn btn-error btn-sm">
                        Remover
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="cancelDelete"></div>
        </div>
    @endif

    {{-- Barra de busca + botão de criar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <input wire:model.live.debounce.200ms="search" type="text"
               placeholder="Buscar categorias..."
               class="input input-bordered input-sm w-full sm:max-w-xs">

        <button wire:click="openForm" class="btn btn-primary btn-sm shrink-0">
            + Nova Categoria
        </button>
    </div>

    {{-- Formulário de criação --}}
    @if($showForm)
        <div class="card bg-primary/5 border border-primary/20 shadow-sm mb-5">
            <div class="card-body py-4">
                <h3 class="font-semibold text-sm text-primary mb-3">Nova categoria</h3>
                <form wire:submit="saveCategory" class="flex flex-wrap items-end gap-4">
                    <div class="form-control flex-1 min-w-[180px]">
                        <label class="label py-1">
                            <span class="label-text text-xs font-medium">Nome</span>
                        </label>
                        <input wire:model="categoryName" type="text" class="input input-bordered input-sm w-full">
                        @error('categoryName')
                            <label class="label py-0.5">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-xs font-medium">Cor</span>
                        </label>
                        <input wire:model="categoryColor" type="color"
                               class="h-9 w-14 rounded border-base-300 cursor-pointer">
                        @error('categoryColor')
                            <label class="label py-0.5">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="$set('showForm', false)"
                                class="btn btn-ghost btn-sm">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="btn btn-primary btn-sm">
                            <span wire:loading.remove>Salvar</span>
                            <span wire:loading class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Tabela de categorias --}}
    <div class="card bg-base-100 shadow-sm">
        @if($categories->isEmpty())
            <div class="card-body items-center py-12">
                <p class="text-sm text-base-content/40">Nenhuma categoria cadastrada.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Chamados</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr class="hover">
                                <td>
                                    @if($editCategoryId === $cat->id)
                                        <div class="flex items-center gap-2">
                                            <input wire:model="editCategoryName" type="text"
                                                   class="input input-bordered input-xs w-48">
                                            <input wire:model="editCategoryColor" type="color"
                                                   class="h-7 w-10 rounded border-base-300 cursor-pointer">
                                            <button wire:click="saveEdit"
                                                    class="btn btn-success btn-xs">✓ Salvar</button>
                                            <button wire:click="cancelEdit"
                                                    class="btn btn-ghost btn-xs">✕</button>
                                        </div>
                                        @error('editCategoryName')
                                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                                        @enderror
                                    @else
                                        <div class="flex items-center gap-2">
                                            @if($cat->color)
                                                <span class="inline-block w-3 h-3 rounded-full shrink-0"
                                                      style="background-color: {{ $cat->color }}"></span>
                                            @endif
                                            <span class="font-medium text-sm">{{ $cat->name }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-sm text-base-content/60">
                                    {{ $cat->tickets_count }} {{ $cat->tickets_count === 1 ? 'chamado' : 'chamados' }}
                                </td>
                                <td class="text-right">
                                    @if($editCategoryId !== $cat->id)
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="startEdit({{ $cat->id }})"
                                                    class="btn btn-ghost btn-xs text-primary">
                                                Editar
                                            </button>
                                            <button wire:click="confirmDelete({{ $cat->id }})"
                                                    class="btn btn-ghost btn-xs text-error">
                                                Remover
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
