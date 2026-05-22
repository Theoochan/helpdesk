<?php

declare(strict_types=1);

namespace App\Modules\Reports\Livewire\Chamados;

use App\Modules\Chamados\Tickets\Models\Ticket;
use App\Modules\Chamados\Tickets\Services\TicketService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TicketReports extends Component
{
    public string $from = '';
    public string $to   = '';

    public function render(TicketService $service)
    {
        $from = $this->from ?: null;
        $to   = $this->to   ?: null;

        $stats = $service->summaryStats();

        $statusRaw   = $service->reportByStatus($from, $to);
        $categoryRaw = $service->reportByCategory($from, $to);
        $timeline    = $service->reportTimeline($from, $to);
        $techStatus  = $service->reportByTechnicianAndStatus($from, $to);

        $statusMap = [
            Ticket::STATUS_OPEN        => ['Aberto',         '#3b82f6'],
            Ticket::STATUS_IN_PROGRESS => ['Em Atendimento', '#f59e0b'],
            Ticket::STATUS_RESOLVED    => ['Resolvido',      '#22c55e'],
            Ticket::STATUS_CLOSED      => ['Fechado',        '#9ca3af'],
            Ticket::STATUS_CANCELLED   => ['Cancelado',      '#ef4444'],
        ];

        $statusLabels = [];
        $statusSeries = [];
        $statusColors = [];
        foreach ($statusMap as $key => [$label, $color]) {
            if (! empty($statusRaw[$key])) {
                $statusLabels[] = $label;
                $statusSeries[] = (int) $statusRaw[$key];
                $statusColors[] = $color;
            }
        }

        $catPalette = ['#3b82f6','#8b5cf6','#ec4899','#f59e0b','#10b981','#ef4444','#06b6d4','#f97316','#6366f1','#84cc16'];
        $catLabels  = array_keys($categoryRaw);
        $catSeries  = array_values($categoryRaw);
        $catColors  = array_map(fn ($i) => $catPalette[$i % count($catPalette)], array_keys($catLabels));

        $chartData = [
            'statusSeries'       => $statusSeries,
            'statusLabels'       => $statusLabels,
            'statusColors'       => $statusColors,
            'categoryLabels'     => $catLabels,
            'categorySeries'     => $catSeries,
            'categoryColors'     => $catColors,
            'timelineCategories' => $timeline['categories'],
            'timelineOpened'     => $timeline['opened'],
            'timelineResolved'   => $timeline['resolved'],
            'techNames'          => $techStatus['techNames'],
            'techSeries'         => $techStatus['series'],
        ];

        $this->dispatch('chartsUpdated', chartData: $chartData);

        return view('livewire.reports.ticket-reports', compact('stats', 'chartData', 'from', 'to'));
    }
}
