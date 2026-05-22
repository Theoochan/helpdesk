<div>
    {{-- Cabeçalho --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-xl font-bold">
            {{ auth()->user()->isTechnician() ? 'Chamados' : 'Meus Chamados' }}
        </h1>
        @if(auth()->user()->isCollaborator())
            <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
                + Abrir Chamado
            </a>
        @endif
    </div>

    {{-- Filtros de status --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach([''=>'Todos', 'open'=>'Aberto', 'in_progress'=>'Em Atendimento', 'resolved'=>'Resolvido', 'closed'=>'Fechado', 'cancelled'=>'Cancelado', 'overdue'=>'Atrasados'] as $val => $label)
            <button wire:click="$set('status', '{{ $val }}')"
                    class="btn btn-sm {{ $status === $val
                        ? ($val === 'overdue' ? 'btn-error' : 'btn-primary')
                        : ($val === 'overdue' ? 'btn-outline btn-error' : 'btn-outline') }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tabela --}}
    <div class="card bg-base-100 shadow-sm">
        @if($tickets->isEmpty())
            <div class="card-body items-center py-16">
                <p class="text-base-content/50">Nenhum chamado encontrado.</p>
                @if(auth()->user()->isCollaborator())
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm mt-2">
                        Abrir primeiro chamado
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            @if(auth()->user()->isTechnician())
                                <th>Solicitante</th>
                            @endif
                            <th>Categoria</th>
                            <th>Prioridade</th>
                            <th>Status</th>
                            <th>Prazo</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            @php
                                $isUnread = is_null($ticket->my_read_at)
                                    || $ticket->my_read_at < $ticket->updated_at->format('Y-m-d H:i:s');
                            @endphp
                            <tr class="{{ $isUnread ? 'bg-primary/5' : '' }} hover">
                                <td class="text-base-content/50">#{{ $ticket->id }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @if($isUnread)
                                            <span class="inline-block w-2 h-2 rounded-full bg-primary shrink-0"
                                                  title="Novo conteúdo"></span>
                                        @endif
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                           class="font-{{ $isUnread ? 'semibold' : 'medium' }} truncate max-w-xs hover:text-primary hover:underline">
                                            {{ $ticket->title }}
                                        </a>
                                    </div>
                                </td>
                                @if(auth()->user()->isTechnician())
                                    <td class="text-sm">{{ $ticket->user->name }}</td>
                                @endif
                                <td class="text-sm text-base-content/60">
                                    {{ $ticket->category?->name ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap"><x-badge :color="$ticket->priorityColor()">{{ $ticket->priorityLabel() }}</x-badge></td>
                                <td class="whitespace-nowrap"><x-badge :color="$ticket->statusColor()">{{ $ticket->statusLabel() }}</x-badge></td>
                                <td class="whitespace-nowrap">
                                    @php $due = $ticket->dueBadge(); @endphp
                                    @if($due)
                                        <x-badge :color="$due['color']">{{ $due['label'] }}</x-badge>
                                    @else
                                        <span class="text-base-content/30">—</span>
                                    @endif
                                </td>
                                <td class="text-xs text-base-content/50">{{ $ticket->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-base-200">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
