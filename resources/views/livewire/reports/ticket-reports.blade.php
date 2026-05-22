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
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body py-4">
            <h2 class="font-semibold text-sm mb-3">Filtrar período</h2>
            <div class="flex flex-wrap items-end gap-4">
                <div class="form-control">
                    <label class="label py-1">
                        <span class="label-text text-xs">De</span>
                    </label>
                    <input wire:model.live="from" type="date" class="input input-bordered input-sm">
                </div>
                <div class="form-control">
                    <label class="label py-1">
                        <span class="label-text text-xs">Até</span>
                    </label>
                    <input wire:model.live="to" type="date" class="input input-bordered input-sm">
                </div>
                @if($from || $to)
                    <button wire:click="$set('from',''); $set('to','')"
                            class="btn btn-ghost btn-sm self-end">
                        ✕ Limpar filtro
                    </button>
                @endif
                <span class="self-end pb-2 text-xs text-primary" wire:loading>Atualizando…</span>
            </div>
        </div>
    </div>

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @foreach([
            'open'        => ['Abertos',        'badge-info',    'bg-info/10'],
            'in_progress' => ['Em Atendimento', 'badge-warning', 'bg-warning/10'],
            'resolved'    => ['Resolvidos',     'badge-success', 'bg-success/10'],
            'closed'      => ['Fechados',       'badge-ghost',   'bg-base-200'],
            'cancelled'   => ['Cancelados',     'badge-error',   'bg-error/10'],
        ] as $key => [$label, $badge, $bg])
            <div class="stat rounded-xl {{ $bg }} shadow-sm">
                <div class="stat-value text-2xl">{{ $stats[$key] }}</div>
                <div class="stat-desc font-medium">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    {{-- ─── Linha 1: Donut × Barras horizontais ──────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">

        <div class="lg:col-span-2 card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-sm">Distribuição por status</h3>
                <div id="chart-status" wire:ignore></div>
            </div>
        </div>

        <div class="lg:col-span-3 card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-sm">Chamados por categoria</h3>
                <div id="chart-category" wire:ignore></div>
            </div>
        </div>
    </div>

    {{-- ─── Linha 2: Evolução temporal ────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <h3 class="card-title text-sm">Volume ao longo do tempo</h3>
                <span class="text-xs text-base-content/40">
                    @if(!$from && !$to) últimos 30 dias @else período selecionado @endif
                </span>
            </div>
            <div id="chart-timeline" wire:ignore></div>
        </div>
    </div>

    {{-- ─── Linha 3: Técnico × Status ────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-sm">Chamados por técnico × status</h3>
            <div id="chart-tech" wire:ignore></div>
        </div>
    </div>

    {{-- ─── Ranking numérico ──────────────────────────────────────────────── --}}
    @php
        $rankTech   = app(\App\Modules\Chamados\Tickets\Services\TicketService::class)->reportByTechnician($from, $to);
        $rankCollab = app(\App\Modules\Chamados\Tickets\Services\TicketService::class)->reportByCollaborator($from, $to);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-0">
                <div class="px-5 py-3 border-b border-base-200">
                    <h3 class="font-semibold text-sm">Ranking por Técnico</h3>
                </div>
                @if(empty($rankTech))
                    <p class="py-8 text-center text-sm text-base-content/40">Nenhum dado para o período selecionado.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Técnico</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rankTech as $row)
                                    @php $max = collect($rankTech)->max('total') ?: 1; @endphp
                                    <tr>
                                        <td class="text-sm">{{ $row['technician']['name'] ?? '—' }}</td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm font-semibold w-6">{{ $row['total'] }}</span>
                                                <progress class="progress progress-primary w-24"
                                                          value="{{ $row['total'] }}" max="{{ $max }}"></progress>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-0">
                <div class="px-5 py-3 border-b border-base-200">
                    <h3 class="font-semibold text-sm">Ranking por Colaborador</h3>
                </div>
                @if(empty($rankCollab))
                    <p class="py-8 text-center text-sm text-base-content/40">Nenhum dado para o período selecionado.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Colaborador</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rankCollab as $row)
                                    @php $maxC = collect($rankCollab)->max('total') ?: 1; @endphp
                                    <tr>
                                        <td class="text-sm">{{ $row['user']['name'] ?? '—' }}</td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm font-semibold w-6">{{ $row['total'] }}</span>
                                                <progress class="progress progress-secondary w-24"
                                                          value="{{ $row['total'] }}" max="{{ $maxC }}"></progress>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
