<div>
    {{-- Filtros --}}
    <div class="mb-5 flex flex-wrap items-center gap-2">
        @foreach([''=>'Todos', 'open'=>'Aberto', 'in_progress'=>'Em Atendimento', 'resolved'=>'Resolvido', 'closed'=>'Fechado'] as $val => $label)
            <button wire:click="$set('status', '{{ $val }}')"
                    class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                           {{ $status === $val ? 'bg-brand-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($tickets->isEmpty())
            <div class="py-16 text-center">
                <p class="text-gray-400 text-sm">Nenhum chamado encontrado.</p>
                @if(auth()->user()->isCollaborator())
                    <a href="{{ route('tickets.create') }}"
                       class="mt-3 inline-block text-sm text-brand-600 font-medium hover:underline">
                        Abrir primeiro chamado →
                    </a>
                @endif
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                        @if(auth()->user()->isTechnician())
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitante</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridade</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($tickets as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-400">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900 truncate max-w-xs">{{ $ticket->title }}</p>
                            </td>
                            @if(auth()->user()->isTechnician())
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $ticket->user->name }}</td>
                            @endif
                            <td class="px-4 py-3">
                                @if($ticket->category)
                                    <span class="text-xs text-gray-500">{{ $ticket->category->name }}</span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :color="$ticket->priorityColor()">{{ $ticket->priorityLabel() }}</x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :color="$ticket->statusColor()">{{ $ticket->statusLabel() }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ $ticket->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="text-xs font-medium text-brand-600 hover:text-brand-700">
                                    Ver →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
