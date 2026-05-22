<div>

    {{-- Modal de confirmação de exclusão --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-data x-transition>
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirmar exclusão</h3>
                <p class="text-sm text-gray-600 mb-5">
                    Tem certeza que deseja remover esta categoria? Esta ação não pode ser desfeita.
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="deleteConfirmed"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg">
                        Remover
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Barra de busca + botão de criar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <input wire:model.live.debounce.200ms="search" type="text"
               placeholder="Buscar categorias..."
               class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500 w-full sm:max-w-xs">

        <button wire:click="openForm"
                class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors shrink-0">
            + Nova Categoria
        </button>
    </div>

    {{-- Formulário de criação --}}
    @if($showForm)
        <div class="bg-brand-50 border border-brand-200 rounded-xl p-5 mb-5">
            <h3 class="text-sm font-semibold text-brand-800 mb-4">Nova categoria</h3>
            <form wire:submit="saveCategory" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nome</label>
                    <input wire:model="categoryName" type="text"
                           class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('categoryName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Cor</label>
                    <input wire:model="categoryColor" type="color"
                           class="h-9 w-14 rounded border-gray-300 cursor-pointer">
                    @error('categoryColor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('showForm', false)"
                            class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg">
                        <span wire:loading.remove>Salvar</span>
                        <span wire:loading>Salvando...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Tabela de categorias --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($categories->isEmpty())
            <div class="py-12 text-center text-sm text-gray-400">Nenhuma categoria cadastrada.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chamados</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($categories as $cat)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                @if($editCategoryId === $cat->id)
                                    <div class="flex items-center gap-2">
                                        <input wire:model="editCategoryName" type="text"
                                               class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 py-1 w-48">
                                        <input wire:model="editCategoryColor" type="color"
                                               class="h-7 w-10 rounded border-gray-300 cursor-pointer">
                                        <button wire:click="saveEdit" class="text-xs text-green-600 font-medium hover:text-green-800">✓ Salvar</button>
                                        <button wire:click="cancelEdit" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                                    </div>
                                    @error('editCategoryName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                @else
                                    <div class="flex items-center gap-2">
                                        @if($cat->color)
                                            <span class="inline-block w-3 h-3 rounded-full"
                                                  style="background-color: {{ $cat->color }}"></span>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900">{{ $cat->name }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $cat->tickets_count }}
                                {{ $cat->tickets_count === 1 ? 'chamado' : 'chamados' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($editCategoryId !== $cat->id)
                                    <div class="flex justify-end gap-3">
                                        <button wire:click="startEdit({{ $cat->id }})"
                                                class="text-xs text-brand-600 hover:text-brand-800 font-medium">
                                            Editar
                                        </button>
                                        <button wire:click="confirmDelete({{ $cat->id }})"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium">
                                            Remover
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
