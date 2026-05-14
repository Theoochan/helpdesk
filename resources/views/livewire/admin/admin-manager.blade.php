<div>

    {{-- Modal de confirmação de exclusão --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-data x-transition>
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirmar exclusão</h3>
                <p class="text-sm text-gray-600 mb-5">
                    Tem certeza que deseja remover este item? Esta ação não pode ser desfeita.
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

    {{-- Abas --}}
    <div class="flex gap-1 mb-6 p-1 bg-gray-100 rounded-xl w-fit">
        @foreach(['technicians' => 'Técnicos', 'collaborators' => 'Colaboradores', 'categories' => 'Categorias'] as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                           {{ $tab === $key
                               ? 'bg-white text-gray-900 shadow-sm'
                               : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Barra de busca + botão de criar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <input wire:model.live.debounce.200ms="search" type="text"
               placeholder="Buscar..."
               class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500 w-full sm:max-w-xs">

        @if($tab !== 'categories')
            <button wire:click="openForm"
                    class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors shrink-0">
                + Novo {{ $tab === 'collaborators' ? 'Colaborador' : 'Técnico / Admin' }}
            </button>
        @else
            <button wire:click="openCategoryForm"
                    class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors shrink-0">
                + Nova Categoria
            </button>
        @endif
    </div>

    {{-- ─── Formulário de usuário ─────────────────────────────────────────────── --}}
    @if($showForm && $tab !== 'categories')
        <div class="bg-brand-50 border border-brand-200 rounded-xl p-5 mb-5">
            <h3 class="text-sm font-semibold text-brand-800 mb-4">
                Cadastrar novo {{ $tab === 'collaborators' ? 'colaborador' : 'usuário técnico' }}
            </h3>
            <form wire:submit="saveUser" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nome</label>
                    <input wire:model="name" type="text"
                           class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">E-mail</label>
                    <input wire:model="email" type="email"
                           class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Senha</label>
                    <input wire:model="password" type="password"
                           class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Papel</label>
                    <select wire:model="role"
                            class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                        @if($tab === 'collaborators')
                            <option value="collaborator">Colaborador</option>
                            <option value="technician">Técnico</option>
                            <option value="admin">Administrador</option>
                        @else
                            <option value="technician">Técnico</option>
                            <option value="admin">Administrador</option>
                        @endif
                    </select>
                    @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 flex justify-end gap-3 pt-1">
                    <button type="button" wire:click="$set('showForm', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
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

    {{-- ─── Formulário de categoria ──────────────────────────────────────────── --}}
    @if($showCategoryForm && $tab === 'categories')
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
                    <button type="button" wire:click="$set('showCategoryForm', false)"
                            class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- ─── Tabela de Técnicos / Admins ─────────────────────────────────────── --}}
    @if($tab === 'technicians')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($technicians->isEmpty())
                <div class="py-12 text-center text-sm text-gray-400">Nenhum técnico cadastrado.</div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">E-mail</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Papel</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Desde</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($technicians as $tech)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $tech->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $tech->email }}</td>
                                <td class="px-4 py-3">
                                    @if($editRoleId === $tech->id)
                                        <div class="flex items-center gap-2">
                                            <select wire:model="editRoleValue"
                                                    class="text-xs rounded border-gray-300 py-1 focus:border-brand-500 focus:ring-brand-500">
                                                <option value="technician">Técnico</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                            <button wire:click="saveRole" class="text-xs text-green-600 font-medium hover:text-green-800">✓</button>
                                            <button wire:click="cancelEdit" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                                        </div>
                                        @error('editRoleValue') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    @else
                                        <div class="flex items-center gap-1">
                                            <x-badge :color="$tech->isAdmin() ? 'purple' : 'blue'">
                                                {{ $tech->isAdmin() ? 'Admin' : 'Técnico' }}
                                            </x-badge>
                                            @if($tech->id !== auth()->id())
                                                <button wire:click="startEditRole({{ $tech->id }})"
                                                        class="text-gray-300 hover:text-gray-500 ml-1" title="Alterar papel">✎</button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $tech->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($tech->id !== auth()->id())
                                        <button wire:click="confirmDelete({{ $tech->id }}, 'user')"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium">Remover</button>
                                    @else
                                        <span class="text-xs text-gray-300">Você</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    {{-- ─── Tabela de Colaboradores ─────────────────────────────────────────── --}}
    @if($tab === 'collaborators')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($collaborators->isEmpty())
                <div class="py-12 text-center text-sm text-gray-400">Nenhum colaborador cadastrado.</div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">E-mail</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Papel</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Desde</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($collaborators as $collab)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $collab->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $collab->email }}</td>
                                <td class="px-4 py-3">
                                    @if($editRoleId === $collab->id)
                                        <div class="flex items-center gap-2">
                                            <select wire:model="editRoleValue"
                                                    class="text-xs rounded border-gray-300 py-1 focus:border-brand-500 focus:ring-brand-500">
                                                <option value="collaborator">Colaborador</option>
                                                <option value="technician">Técnico</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                            <button wire:click="saveRole" class="text-xs text-green-600 font-medium hover:text-green-800">✓</button>
                                            <button wire:click="cancelEdit" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                                        </div>
                                        @error('editRoleValue') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    @else
                                        <div class="flex items-center gap-1">
                                            <x-badge color="gray">Colaborador</x-badge>
                                            <button wire:click="startEditRole({{ $collab->id }})"
                                                    class="text-gray-300 hover:text-gray-500 ml-1" title="Promover / rebaixar">✎</button>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $collab->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button wire:click="confirmDelete({{ $collab->id }}, 'user')"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">Remover</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    {{-- ─── Tabela de Categorias ────────────────────────────────────────────── --}}
    @if($tab === 'categories')
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
                                            <button wire:click="saveEditCategory" class="text-xs text-green-600 font-medium hover:text-green-800">✓ Salvar</button>
                                            <button wire:click="cancelEditCategory" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                                        </div>
                                        @error('editCategoryName')  <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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
                                            <button wire:click="startEditCategory({{ $cat->id }})"
                                                    class="text-xs text-brand-600 hover:text-brand-800 font-medium">
                                                Editar
                                            </button>
                                            <button wire:click="confirmDelete({{ $cat->id }}, 'category')"
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
    @endif

</div>
