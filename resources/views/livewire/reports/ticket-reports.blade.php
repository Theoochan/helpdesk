{{-- @assets: Livewire garante carregamento único e correto da lib externa --}}
@assets
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
@endassets

{{-- @script: roda UMA vez após o componente ser montado; $wire está disponível --}}
@script
<script>
(() => {
    /* ─── Instâncias dos gráficos ─────────────────────────────────────── */
    const charts = {};

    /* ─── Opções de cada gráfico ─────────────────────────────────────── */
    function optStatus(d) {
        return {
            chart: { type: 'donut', height: 300, toolbar: { show: false } },
            series: d.statusSeries,
            labels: d.statusLabels,
            colors: d.statusColors.length ? d.statusColors : ['#9ca3af'],
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: { pie: { donut: { size: '60%' } } },
            dataLabels: { enabled: true, formatter: (v) => Math.round(v) + '%' },
            noData: { text: 'Nenhum chamado no período', style: { color: '#9ca3af' } },
            tooltip: { y: { formatter: (v) => v + ' chamados' } },
        };
    }

    function optCategory(d) {
        return {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Chamados', data: d.categorySeries }],
            xaxis: { categories: d.categoryLabels, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } },
            colors: d.categoryColors.length ? d.categoryColors : ['#3b82f6'],
            dataLabels: { enabled: true, offsetX: 18, style: { fontSize: '11px', colors: ['#374151'] }, formatter: (v) => Math.round(v) },
            grid: { xaxis: { lines: { show: false } } },
            noData: { text: 'Nenhuma categoria no período', style: { color: '#9ca3af' } },
            tooltip: { y: { formatter: (v) => v + ' chamados' } },
        };
    }

    function optTimeline(d) {
        return {
            chart: { type: 'area', height: 280, toolbar: { show: true }, zoom: { enabled: true } },
            series: [
                { name: 'Abertos',    data: d.timelineOpened   },
                { name: 'Resolvidos', data: d.timelineResolved  },
            ],
            xaxis: {
                categories: d.timelineCategories,
                tickAmount: Math.min(d.timelineCategories.length, 15),
                labels: { style: { fontSize: '11px' }, rotate: -45 },
            },
            yaxis: { min: 0, labels: { formatter: (v) => Math.round(v) } },
            colors: ['#3b82f6', '#22c55e'],
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: { shade: 'light', type: 'vertical', opacityFrom: 0.35, opacityTo: 0.03 },
            },
            legend: { position: 'top' },
            noData: { text: 'Sem dados no período', style: { color: '#9ca3af' } },
            tooltip: { y: { formatter: (v) => v + ' chamados' } },
        };
    }

    function optTech(d) {
        return {
            chart: { type: 'bar', stacked: true, height: 320, toolbar: { show: false } },
            series: d.techSeries.map(s => ({ name: s.name, data: s.data })),
            xaxis: { categories: d.techNames, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { formatter: (v) => Math.round(v) } },
            colors: d.techSeries.length ? d.techSeries.map(s => s.color) : ['#9ca3af'],
            plotOptions: { bar: { horizontal: false, borderRadius: 3, columnWidth: '55%' } },
            legend: { position: 'top', fontSize: '12px' },
            noData: { text: 'Nenhum técnico com chamados no período', style: { color: '#9ca3af' } },
            tooltip: { y: { formatter: (v) => v + ' chamados' } },
        };
    }

    /* ─── Criar / destruir todos os gráficos ────────────────────────── */
    function buildAll(d) {
        const map = {
            status:   ['#chart-status',   optStatus(d)],
            category: ['#chart-category', optCategory(d)],
            timeline: ['#chart-timeline', optTimeline(d)],
            tech:     ['#chart-tech',     optTech(d)],
        };

        Object.entries(map).forEach(([key, [sel, opts]]) => {
            if (charts[key]) { charts[key].destroy(); }
            const el = document.querySelector(sel);
            if (el) {
                charts[key] = new ApexCharts(el, opts);
                charts[key].render();
            }
        });
    }

    /* ─── Atualizar sem destruir (evita flickering) ──────────────────── */
    function updateAll(d) {
        const map = {
            status:   optStatus(d),
            category: optCategory(d),
            timeline: optTimeline(d),
            tech:     optTech(d),
        };
        Object.entries(map).forEach(([key, opts]) => {
            if (charts[key]) charts[key].updateOptions(opts, false, true);
        });
    }

    /* ─── Bootstrap ─────────────────────────────────────────────────── */
    buildAll(@json($chartData));

    $wire.on('chartsUpdated', ({ chartData }) => updateAll(chartData));
})();
</script>
@endscript

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Filtrar período</h2>
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">De</label>
                <input wire:model.live="from" type="date"
                       class="block rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Até</label>
                <input wire:model.live="to" type="date"
                       class="block rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            @if($from || $to)
                <button wire:click="$set('from',''); $set('to','')"
                        class="self-end px-3 py-2 text-xs text-gray-500 hover:text-red-600 border border-gray-300 rounded-lg hover:border-red-300 transition-colors">
                    ✕ Limpar filtro
                </button>
            @endif
            <span class="self-end pb-2 text-xs text-brand-500" wire:loading>Atualizando…</span>
        </div>
    </div>

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @foreach([
            'open'        => ['Abertos',        'text-blue-700',   'bg-blue-50',   'border-blue-200'],
            'in_progress' => ['Em Atendimento', 'text-yellow-700', 'bg-yellow-50', 'border-yellow-200'],
            'resolved'    => ['Resolvidos',     'text-green-700',  'bg-green-50',  'border-green-200'],
            'closed'      => ['Fechados',       'text-gray-700',   'bg-gray-50',   'border-gray-200'],
            'cancelled'   => ['Cancelados',     'text-red-700',    'bg-red-50',    'border-red-200'],
        ] as $key => [$label, $text, $bg, $border])
            <div class="rounded-xl border p-4 text-center {{ $bg }} {{ $border }}">
                <div class="text-2xl font-bold {{ $text }}">{{ $stats[$key] }}</div>
                <div class="text-xs font-medium mt-0.5 {{ $text }}">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    {{-- ─── Linha 1: Donut × Barras horizontais ──────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">

        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Distribuição por status</h3>
            <div id="chart-status" wire:ignore></div>
        </div>

        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Chamados por categoria</h3>
            <div id="chart-category" wire:ignore></div>
        </div>
    </div>

    {{-- ─── Linha 2: Evolução temporal ────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Volume ao longo do tempo</h3>
            <span class="text-xs text-gray-400">
                @if(!$from && !$to) últimos 30 dias @else período selecionado @endif
            </span>
        </div>
        <div id="chart-timeline" wire:ignore></div>
    </div>

    {{-- ─── Linha 3: Técnico × Status ────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Chamados por técnico × status</h3>
        <div id="chart-tech" wire:ignore></div>
    </div>

    {{-- ─── Ranking numérico ──────────────────────────────────────────────── --}}
    @php
        $rankTech   = app(\App\Modules\Chamados\Tickets\Services\TicketService::class)->reportByTechnician($from, $to);
        $rankCollab = app(\App\Modules\Chamados\Tickets\Services\TicketService::class)->reportByCollaborator($from, $to);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Ranking por Técnico</h3>
            </div>
            @if(empty($rankTech))
                <p class="py-8 text-center text-sm text-gray-400">Nenhum dado para o período selecionado.</p>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                            <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rankTech as $row)
                            @php $max = collect($rankTech)->max('total') ?: 1; @endphp
                            <tr>
                                <td class="px-5 py-2.5 text-sm text-gray-800">{{ $row['technician']['name'] ?? '—' }}</td>
                                <td class="px-5 py-2.5">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-semibold text-gray-900 w-6">{{ $row['total'] }}</span>
                                        <div class="flex-1 bg-gray-100 rounded-full h-1.5 max-w-[120px]">
                                            <div class="bg-brand-500 h-1.5 rounded-full"
                                                 style="width: {{ round($row['total'] / $max * 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Ranking por Colaborador</h3>
            </div>
            @if(empty($rankCollab))
                <p class="py-8 text-center text-sm text-gray-400">Nenhum dado para o período selecionado.</p>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Colaborador</th>
                            <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rankCollab as $row)
                            @php $maxC = collect($rankCollab)->max('total') ?: 1; @endphp
                            <tr>
                                <td class="px-5 py-2.5 text-sm text-gray-800">{{ $row['user']['name'] ?? '—' }}</td>
                                <td class="px-5 py-2.5">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-semibold text-gray-900 w-6">{{ $row['total'] }}</span>
                                        <div class="flex-1 bg-gray-100 rounded-full h-1.5 max-w-[120px]">
                                            <div class="bg-purple-500 h-1.5 rounded-full"
                                                 style="width: {{ round($row['total'] / $maxC * 100) }}%"></div>
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

</div>
