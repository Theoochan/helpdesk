<div>
    <div class="mb-5 flex flex-wrap items-center gap-2">
        @foreach(['' => 'Todas', 'pending' => 'Pendente', 'in_progress' => 'Em Andamento', 'done' => 'Finalizada'] as $val => $label)
            <button wire:click="$set('status', '{{ $val }}')"
                    class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                           {{ $status === $val ? 'bg-brand-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                {{ $label }}
            </button>
        @endforeach

        <a href="{{ route('orders.create') }}"
           class="ml-auto px-4 py-1.5 rounded-lg text-xs font-medium text-white bg-brand-600 hover:bg-brand-700 transition-colors">
            + Nova OS
        </a>
    </div>

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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-400">#{{ $order->id }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 max-w-xs truncate">
                                {{ $order->title }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $order->requester->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $order->assignedTo->name }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$order->priorityColor()">{{ $order->priorityLabel() }}</x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :color="$order->statusColor()">{{ $order->statusLabel() }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ $order->created_at->format('d/m/Y') }}
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
