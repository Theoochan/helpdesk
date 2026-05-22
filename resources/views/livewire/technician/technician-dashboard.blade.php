<div>
    {{-- Cards de status --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
        $cards = [
            ['label'=>'Abertos',       'key'=>'open',        'color'=>'text-info',    'icon'=>'📬'],
            ['label'=>'Em Atendimento','key'=>'in_progress', 'color'=>'text-warning', 'icon'=>'🔧'],
            ['label'=>'Resolvidos',    'key'=>'resolved',    'color'=>'text-success', 'icon'=>'✅'],
            ['label'=>'Fechados',      'key'=>'closed',      'color'=>'text-base-content/50', 'icon'=>'🔒'],
        ];
        @endphp

        @foreach($cards as $card)
            <div class="stat bg-base-100 rounded-xl shadow-sm cursor-pointer
                        {{ $statusFilter === $card['key'] ? 'ring-2 ring-primary' : 'hover:bg-base-200' }} transition-colors"
                 wire:click="$set('statusFilter', '{{ $statusFilter === $card['key'] ? '' : $card['key'] }}')">
                <div class="stat-figure text-2xl">{{ $card['icon'] }}</div>
                <div class="stat-value text-2xl {{ $card['color'] }}">{{ $stats[$card['key']] }}</div>
                <div class="stat-desc">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Tabela de chamados --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="flex items-center justify-between px-5 py-3 border-b border-base-200">
                <h2 class="font-semibold text-sm">
                    {{ $statusFilter
                        ? 'Chamados: ' . ['open'=>'Abertos','in_progress'=>'Em Atendimento','resolved'=>'Resolvidos','closed'=>'Fechados'][$statusFilter]
                        : 'Todos os Chamados' }}
                </h2>
                @if($statusFilter)
                    <button wire:click="$set('statusFilter', '')" class="btn btn-ghost btn-xs text-base-content/50">
                        Limpar filtro ×
                    </button>
                @endif
            </div>

            @if($recentTickets->isEmpty())
                <div class="py-12 text-center text-sm text-base-content/40">
                    Nenhum chamado encontrado.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Título</th>
                                <th>Solicitante</th>
                                <th>Status</th>
                                <th>Técnico</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTickets as $ticket)
                                <tr class="hover">
                                    <td class="text-base-content/50">#{{ $ticket->id }}</td>
                                    <td class="max-w-xs truncate">
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                           class="font-medium hover:text-primary hover:underline">
                                            {{ $ticket->title }}
                                        </a>
                                    </td>
                                    <td class="text-sm">{{ $ticket->user->name }}</td>
                                    <td class="whitespace-nowrap">
                                        <x-badge :color="$ticket->statusColor()">{{ $ticket->statusLabel() }}</x-badge>
                                    </td>
                                    <td class="text-sm text-base-content/60">
                                        {{ $ticket->technician?->name ?? '—' }}
                                    </td>
                                    <td class="text-xs text-base-content/50">{{ $ticket->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-base-200">
                    {{ $recentTickets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
