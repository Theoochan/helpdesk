<div>
    {{-- Cards de status --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
        $cards = [
            ['label'=>'Abertos',       'key'=>'open',        'color'=>'blue',   'icon'=>'📬'],
            ['label'=>'Em Atendimento','key'=>'in_progress', 'color'=>'yellow', 'icon'=>'🔧'],
            ['label'=>'Resolvidos',    'key'=>'resolved',    'color'=>'green',  'icon'=>'✅'],
            ['label'=>'Fechados',      'key'=>'closed',      'color'=>'gray',   'icon'=>'🔒'],
        ];
        @endphp

        @foreach($cards as $card)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 cursor-pointer hover:border-brand-300 transition-colors
                        {{ $statusFilter === $card['key'] ? 'ring-2 ring-brand-500' : '' }}"
                 wire:click="$set('statusFilter', '{{ $statusFilter === $card['key'] ? '' : $card['key'] }}')">
                <div class="text-2xl mb-1">{{ $card['icon'] }}</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats[$card['key']] }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Tabela de chamados recentes --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800">
                {{ $statusFilter ? 'Chamados: ' . ['open'=>'Abertos','in_progress'=>'Em Atendimento','resolved'=>'Resolvidos','closed'=>'Fechados'][$statusFilter] : 'Todos os Chamados' }}
            </h2>
            @if($statusFilter)
                <button wire:click="$set('statusFilter', '')" class="text-xs text-gray-400 hover:text-gray-600">
                    Limpar filtro ×
                </button>
            @endif
        </div>

        @if($recentTickets->isEmpty())
            <div class="py-12 text-center text-sm text-gray-400">
                Nenhum chamado encontrado.
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($recentTickets as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-400">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 max-w-xs truncate">
                                {{ $ticket->title }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $ticket->user->name }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$ticket->statusColor()">{{ $ticket->statusLabel() }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $ticket->technician ? $ticket->technician->name : '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ $ticket->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="text-xs font-medium text-brand-600 hover:text-brand-700">Ver →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $recentTickets->links() }}
            </div>
        @endif
    </div>
</div>
