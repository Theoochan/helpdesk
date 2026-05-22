<div>
    {{-- Cabeçalho --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-xl font-bold">OS Internas</h1>
        <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm">
            + Nova OS
        </a>
    </div>

    {{-- Abas Minhas OS / Todas as OS --}}
    <div role="tablist" class="tabs tabs-bordered mb-5">
        <button role="tab" wire:click="$set('tab', 'mine')"
                class="tab {{ $tab === 'mine' ? 'tab-active' : '' }}">
            Minhas OS
        </button>
        <button role="tab" wire:click="$set('tab', 'all')"
                class="tab {{ $tab === 'all' ? 'tab-active' : '' }}">
            Todas as OS
        </button>
    </div>

    {{-- Filtros de status --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach(['' => 'Todas', 'pending' => 'Pendente', 'in_progress' => 'Em Andamento', 'done' => 'Finalizada', 'cancelled' => 'Cancelada', 'overdue' => 'Atrasadas'] as $val => $label)
            <button wire:click="$set('status', '{{ $val }}')"
                    class="btn btn-sm {{ $status === $val
                        ? ($val === 'overdue' ? 'btn-error' : 'btn-primary')
                        : ($val === 'overdue' ? 'btn-outline btn-error' : 'btn-outline') }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Aviso de somente leitura na aba "Todas" para não-admin --}}
    @if($tab === 'all' && !auth()->user()->isAdmin())
        <div role="alert" class="alert alert-info alert-soft mb-4 text-xs">
            <span>Visualização geral — você pode ver todas as OS, mas só pode agir nas suas.</span>
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm">
        @if($orders->isEmpty())
            <div class="card-body items-center py-16">
                <p class="text-base-content/50">Nenhuma OS interna encontrada.</p>
                <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm mt-2">
                    Criar primeira OS →
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Solicitante</th>
                            <th>Responsável</th>
                            <th>Prioridade</th>
                            <th>Status</th>
                            <th>Prazo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            @php
                                $isUnread = is_null($order->my_read_at)
                                    || $order->my_read_at < $order->updated_at->format('Y-m-d H:i:s');
                                $dueBadge = $order->dueBadge();
                            @endphp
                            <tr class="{{ $isUnread ? 'bg-primary/5' : '' }} hover">
                                <td class="text-base-content/50">#{{ $order->id }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @if($isUnread)
                                            <span class="inline-block w-2 h-2 rounded-full bg-primary shrink-0"
                                                  title="Novo conteúdo"></span>
                                        @endif
                                        <a href="{{ route('orders.show', $order) }}"
                                           class="font-{{ $isUnread ? 'semibold' : 'medium' }} truncate max-w-xs hover:text-primary hover:underline">
                                            {{ $order->title }}
                                        </a>
                                        @if($order->hasPendingTransfer())
                                            <span class="badge badge-sm badge-secondary shrink-0">Transferência</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-sm">{{ $order->requester->name }}</td>
                                <td class="text-sm">{{ $order->assignedTo->name }}</td>
                                <td class="whitespace-nowrap"><x-badge :color="$order->priorityColor()">{{ $order->priorityLabel() }}</x-badge></td>
                                <td class="whitespace-nowrap"><x-badge :color="$order->statusColor()">{{ $order->statusLabel() }}</x-badge></td>
                                <td class="whitespace-nowrap">
                                    @if($dueBadge)
                                        <x-badge :color="$dueBadge['color']">{{ $dueBadge['label'] }}</x-badge>
                                    @else
                                        <span class="text-base-content/30">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-base-200">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
