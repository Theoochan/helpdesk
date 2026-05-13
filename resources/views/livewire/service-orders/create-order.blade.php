<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-xl font-semibold text-gray-900 mb-6">Nova OS Interna</h1>

        <form wire:submit="save" class="space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input wire:model="title" type="text"
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                       placeholder="Ex: Substituição de HD no servidor 02">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                <select wire:model="priority"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
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
                }" class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Responsável <span class="text-red-500">*</span>
                </label>

                <div @click="open = true"
                     class="flex items-center justify-between w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm bg-white cursor-pointer hover:border-brand-400 focus-within:ring-1 focus-within:ring-brand-500">
                    <span x-text="selectedName || 'Buscar técnico...'" class="text-gray-500"></span>
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <div x-show="open" @click.outside="open = false"
                     class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg">
                    <div class="p-2">
                        <input wire:model.live.debounce.200ms="techSearch"
                               type="text" placeholder="Digite para filtrar..."
                               class="w-full rounded-md border-gray-200 text-sm focus:ring-brand-500 focus:border-brand-500"
                               @click.stop>
                    </div>
                    <ul class="max-h-48 overflow-y-auto divide-y divide-gray-100">
                        @forelse($technicians as $tech)
                            <li @click="select({{ $tech->id }}, '{{ $tech->name }}')"
                                class="px-4 py-2.5 text-sm hover:bg-brand-50 cursor-pointer flex items-center justify-between">
                                <span>{{ $tech->name }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded-full
                                    {{ $tech->isAdmin() ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                                    {{ $tech->isAdmin() ? 'Admin' : 'Técnico' }}
                                </span>
                            </li>
                        @empty
                            <li class="px-4 py-3 text-sm text-gray-400 text-center">Nenhum técnico encontrado.</li>
                        @endforelse
                    </ul>
                </div>
                @error('assigned_to_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descrição <span class="text-red-500">*</span>
                </label>
                <textarea wire:model="description" rows="5"
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                          placeholder="Descreva a tarefa técnica em detalhes..."></textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('orders.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-brand-600 border border-transparent rounded-lg hover:bg-brand-700 transition-colors"
                        wire:loading.attr="disabled" wire:loading.class="opacity-75">
                    <span wire:loading.remove>Criar OS</span>
                    <span wire:loading>Salvando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
