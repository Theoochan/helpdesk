<div>
    {{-- Modal de confirmação de exclusão --}}
    @if($confirmDeleteId)
        <div class="modal modal-open" x-data x-transition>
            <div class="modal-box max-w-sm">
                <h3 class="font-bold text-lg mb-2">Confirmar exclusão</h3>
                <p class="text-sm text-base-content/70 mb-5">
                    Tem certeza que deseja remover este usuário? Esta ação não pode ser desfeita.
                </p>
                <div class="modal-action">
                    <button wire:click="cancelDelete" class="btn btn-ghost btn-sm">
                        Cancelar
                    </button>
                    <button wire:click="deleteTechnician" class="btn btn-error btn-sm">
                        Remover
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="cancelDelete"></div>
        </div>
    @endif

    {{-- Cabeçalho e busca --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <input wire:model.live.debounce.200ms="search" type="text"
               placeholder="Buscar por nome ou e-mail..."
               class="input input-bordered input-sm w-full sm:max-w-xs">
        <button wire:click="openForm" class="btn btn-primary btn-sm shrink-0">
            + Novo Técnico / Admin
        </button>
    </div>

    {{-- Formulário de criação (colapsável) --}}
    @if($showForm)
        <div class="card bg-primary/5 border border-primary/20 shadow-sm mb-5">
            <div class="card-body">
                <h3 class="font-semibold text-sm text-primary mb-3">Cadastrar novo usuário técnico</h3>
                <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-xs font-medium">Nome</span>
                        </label>
                        <input wire:model="name" type="text" class="input input-bordered input-sm w-full">
                        @error('name')
                            <label class="label py-0.5">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-xs font-medium">E-mail</span>
                        </label>
                        <input wire:model="email" type="email" class="input input-bordered input-sm w-full">
                        @error('email')
                            <label class="label py-0.5">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-xs font-medium">Senha</span>
                        </label>
                        <input wire:model="password" type="password" class="input input-bordered input-sm w-full">
                        @error('password')
                            <label class="label py-0.5">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-xs font-medium">Papel</span>
                        </label>
                        <select wire:model="role" class="select select-bordered select-sm w-full">
                            <option value="technician">Técnico</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-3 pt-1">
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

    {{-- Tabela de técnicos --}}
    <div class="card bg-base-100 shadow-sm">
        @if($technicians->isEmpty())
            <div class="card-body items-center py-12">
                <p class="text-sm text-base-content/40">Nenhum técnico cadastrado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Papel</th>
                            <th>Desde</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($technicians as $tech)
                            <tr class="hover">
                                <td class="font-medium">{{ $tech->name }}</td>
                                <td class="text-sm text-base-content/70">{{ $tech->email }}</td>
                                <td>
                                    <x-badge :color="$tech->isAdmin() ? 'purple' : 'blue'">
                                        {{ $tech->isAdmin() ? 'Admin' : 'Técnico' }}
                                    </x-badge>
                                </td>
                                <td class="text-xs text-base-content/40">
                                    {{ $tech->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-right">
                                    @if($tech->id !== auth()->id())
                                        <button wire:click="confirmDelete({{ $tech->id }})"
                                                class="btn btn-ghost btn-xs text-error">
                                            Remover
                                        </button>
                                    @else
                                        <span class="text-xs text-base-content/30">Você</span>
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
