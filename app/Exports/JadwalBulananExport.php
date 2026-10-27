<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JadwalBulananExport implements FromView, ShouldAutoSize
{
    public function __construct(
        private readonly Collection $users,
        private readonly Collection $days,
        private readonly Collection $schedules,
        private readonly string $month,
        private readonly string $clientName
    ) {}

    public function view(): View
    {
        return view('admin.jadwalUser.export-table', [
            'users' => $this->users,
            'days' => $this->days,
            'schedules' => $this->schedules,
            'month' => $this->month,
            'clientName' => $this->clientName,
        ]);
    }
}
