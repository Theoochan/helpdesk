<?php

namespace App\Livewire\Reports;

use App\Services\TicketService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TicketReports extends Component
{
    public string $from = '';
    public string $to   = '';
    public string $groupBy = 'technician';

    public function render(TicketService $service)
    {
        $from = $this->from ?: null;
        $to   = $this->to   ?: null;

        $rows = $this->groupBy === 'technician'
            ? $service->reportByTechnician($from, $to)
            : $service->reportByCollaborator($from, $to);

        $stats = $service->summaryStats();

        return view('livewire.reports.ticket-reports', compact('rows', 'stats'));
    }
}
