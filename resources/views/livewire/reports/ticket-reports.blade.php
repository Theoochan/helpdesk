<div>
    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Filtrar Relatório</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">De</label>
                <input wire:model.live="from" type="date"
                       class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Até</label>
                <input wire:model.live="to" type="date"
                       class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Agrupar por</label>
                <select wire:model.live="groupBy"
                        class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="technician">Técnico</option>
                    <option value="collaborator">Colaborador</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Cards resumo --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach(['open'=>['Abertos','blue'], 'in_progress'=>['Em Atendimento','yellow'], 'resolved'=>['Resolvidos','green'], 'closed'=>['Fechados','gray']] as $key => [$label, $color])
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $stats[$key] }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    {{-- Tabela de resultados --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">
                Chamados por {{ $groupBy === 'technician' ? 'Técnico' : 'Colaborador' }}
            </h3>
        </div>

        @if(empty($rows))
            <div class="py-12 text-center text-sm text-gray-400">
                Nenhum dado para o período selecionado.
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            {{ $groupBy === 'technician' ? 'Técnico' : 'Colaborador' }}
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total de Chamados</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($rows as $row)
                        <tr>
                            <td class="px-5 py-3 text-sm text-gray-800">
                                @if($groupBy === 'technician')
                                    {{ $row['technician']['name'] ?? '—' }}
                                @else
                                    {{ $row['user']['name'] ?? '—' }}
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-semibold text-gray-900">{{ $row['total'] }}</span>
                                    <div class="flex-1 bg-gray-100 rounded-full h-2 max-w-xs">
                                        @php $max = collect($rows)->max('total') ?: 1; @endphp
                                        <div class="bg-brand-500 h-2 rounded-full" style="width: {{ round($row['total'] / $max * 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
