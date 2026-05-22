<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h1 class="card-title text-xl mb-2">Nova OS Interna</h1>

            <form wire:submit="save" class="space-y-5">

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Título <span class="text-error">*</span></span>
                    </label>
                    <input wire:model="title" type="text"
                           class="input input-bordered w-full"
                           placeholder="Ex: Substituição de HD no servidor 02">
                    @error('title')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Prioridade</span>
                    </label>
                    <select wire:model="priority" class="select select-bordered w-full">
                        <option value="low">Baixa</option>
                        <option value="medium">Média</option>
                        <option value="high">Alta</option>
                    </select>
                </div>

                {{-- Dropdown com busca Alpine para selecionar responsável --}}
                <div x-data="{
                        open: false,
                        selectedName: '',
                        select(id, name) {
                            $wire.assigned_to_id = id;
                            $wire.techSearch = '';
                            this.selectedName = name;
                            this.open = false;
                        }
                    }" class="form-control relative">
                    <label class="label">
                        <span class="label-text font-medium">Responsável <span class="text-error">*</span></span>
                    </label>

                    <div @click="open = true"
                         class="input input-bordered w-full flex items-center justify-between cursor-pointer">
                        <span x-text="selectedName || 'Buscar técnico...'"
                              :class="selectedName ? 'text-base-content' : 'text-base-content/40'"></span>
                        <svg class="h-4 w-4 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    <div x-show="open" @click.outside="open = false"
                         class="absolute z-20 mt-1 w-full bg-base-100 border border-base-300 rounded-lg shadow-lg top-full">
                        <div class="p-2">
                            <input wire:model.live.debounce.200ms="techSearch"
                                   type="text" placeholder="Digite para filtrar..."
                                   class="input input-bordered input-sm w-full"
                                   @click.stop>
                        </div>
                        <ul class="max-h-48 overflow-y-auto">
                            @forelse($technicians as $tech)
                                <li @click="select({{ $tech->id }}, '{{ $tech->name }}')"
                                    class="px-4 py-2.5 text-sm hover:bg-base-200 cursor-pointer flex items-center justify-between">
                                    <span>{{ $tech->name }}</span>
                                    <span class="badge badge-sm {{ $tech->isAdmin() ? 'badge-secondary' : 'badge-info' }}">
                                        {{ $tech->isAdmin() ? 'Admin' : 'Técnico' }}
                                    </span>
                                </li>
                            @empty
                                <li class="px-4 py-3 text-sm text-base-content/40 text-center">Nenhum técnico encontrado.</li>
                            @endforelse
                        </ul>
                    </div>
                    @error('assigned_to_id')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Prazo</span>
                        <span class="label-text-alt text-base-content/40">opcional</span>
                    </label>
                    <input wire:model="due_date" type="date"
                           min="{{ now()->toDateString() }}"
                           class="input input-bordered w-full">
                    @error('due_date')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Descrição <span class="text-error">*</span></span>
                    </label>
                    <textarea wire:model="description" rows="5"
                              class="textarea textarea-bordered w-full"
                              placeholder="Descreva a tarefa técnica em detalhes..."></textarea>
                    @error('description')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('orders.index') }}" class="btn btn-ghost btn-sm">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Criar OS</span>
                        <span wire:loading class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
