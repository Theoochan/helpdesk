<div>
    {{-- Modal de confirmação de exclusão --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             x-data x-transition>
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirmar exclusão</h3>
                <p class="text-sm text-gray-600 mb-5">
                    Tem certeza que deseja remover este usuário? Esta ação não pode ser desfeita.
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="deleteTechnician"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg">
                        Remover
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Cabeçalho e busca --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <input wire:model.live.debounce.200ms="search" type="text"
               placeholder="Buscar por nome ou e-mail..."
               class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500 w-full sm:max-w-xs">
        <button wire:click="openForm"
                class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors shrink-0">
            + Novo Técnico / Admin
        </button>
    </div>

    {{-- Formulário de criação (colapsável) --}}
    @if($showForm)
        <div class="bg-brand-50 border border-brand-200 rounded-xl p-5 mb-5">
            <h3 class="text-sm font-semibold text-brand-800 mb-4">Cadastrar novo usuário técnico</h3>
            <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                        <option value="technician">Técnico</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="sm:col-span-2 flex justify-end gap-3 pt-1">
                    <button type="button" wire:click="$set('showForm', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Salvar</span>
                        <span wire:loading>Salvando...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Tabela de técnicos --}}
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
                                <x-badge :color="$tech->isAdmin() ? 'purple' : 'blue'">
                                    {{ $tech->isAdmin() ? 'Admin' : 'Técnico' }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ $tech->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($tech->id !== auth()->id())
                                    <button wire:click="confirmDelete({{ $tech->id }})"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">
                                        Remover
                                    </button>
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
</div>
