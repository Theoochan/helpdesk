<div>
    {{-- Abas Minhas OS / Todas as OS --}}
    <div class="flex gap-1 mb-5 border-b border-gray-200">
        <button wire:click="$set('tab', 'mine')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors
                       {{ $tab === 'mine' ? 'bg-white border border-b-white border-gray-200 text-brand-700 -mb-px' : 'text-gray-500 hover:text-gray-700' }}">
            Minhas OS
        </button>
        <button wire:click="$set('tab', 'all')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors
                       {{ $tab === 'all' ? 'bg-white border border-b-white border-gray-200 text-brand-700 -mb-px' : 'text-gray-500 hover:text-gray-700' }}">
            Todas as OS
        </button>
    </div>

    {{-- Filtros de status --}}
    <div class="mb-5 flex flex-wrap items-center gap-2">
        @foreach(['' => 'Todas', 'pending' => 'Pendente', 'in_progress' => 'Em Andamento', 'done' => 'Finalizada', 'cancelled' => 'Cancelada', 'overdue' => 'Atrasadas'] as $val => $label)
            <button wire:click="$set('status', '{{ $val }}')"
                    class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                           {{ $status === $val
                               ? ($val === 'overdue' ? 'bg-red-600 text-white' : 'bg-brand-600 text-white')
                               : ($val === 'overdue' ? 'bg-white border border-red-300 text-red-600 hover:bg-red-50' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50') }}">
                {{ $label }}
            </button>
        @endforeach

        <a href="{{ route('orders.create') }}"
           class="ml-auto px-4 py-1.5 rounded-lg text-xs font-medium text-white bg-brand-600 hover:bg-brand-700 transition-colors">
            + Nova OS
        </a>
    </div>

    {{-- Aviso de somente leitura na aba "Todas" para não-admin --}}
    @if($tab === 'all' && !auth()->user()->isAdmin())
        <div class="mb-4 px-4 py-2.5 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
            Visualização geral — você pode ver todas as OS, mas só pode agir nas suas.
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($orders->isEmpty())
            <div class="py-16 text-center">
                <p class="text-gray-400 text-sm">Nenhuma OS interna encontrada.</p>
                <a href="{{ route('orders.create') }}" class="mt-2 inline-block text-sm text-brand-600 font-medium hover:underline">
                    Criar primeira OS →
                </a>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responsável</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridade</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prazo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($orders as $order)
                        @php
                            $isUnread = is_null($order->my_read_at)
                                || $order->my_read_at < $order->updated_at->format('Y-m-d H:i:s');
                            $dueBadge = $order->dueBadge();
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $isUnread ? 'bg-blue-50/40' : '' }}">
                            <td class="px-4 py-3 text-sm text-gray-400">#{{ $order->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($isUnread)
                                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500 shrink-0" title="Novo conteúdo"></span>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900 max-w-xs truncate {{ $isUnread ? 'font-semibold' : '' }}">
                                        {{ $order->title }}
                                    </span>
                                    @if($order->hasPendingTransfer())
                                        <span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full shrink-0">Transferência</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $order->requester->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $order->assignedTo->name }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$order->priorityColor()">{{ $order->priorityLabel() }}</x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :color="$order->statusColor()">{{ $order->statusLabel() }}</x-badge>
                            </td>
                            <td class="px-4 py-3">
                                @if($dueBadge)
                                    <x-badge :color="$dueBadge['color']">{{ $dueBadge['label'] }}</x-badge>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('orders.show', $order) }}"
                                   class="text-xs font-medium text-brand-600 hover:text-brand-700">Ver →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
