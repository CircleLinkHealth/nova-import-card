<?php

namespace App\Reports\Sales\Practice\Sections;

use App\Practice;
use App\Reports\Sales\PracticeReportable;
use App\Reports\Sales\SalesReportSection;
use App\Reports\Sales\StatsHelper;
use Carbon\Carbon;

class FinancialSummary extends SalesReportSection
{
    protected $clhpppm;
    protected $service;

    public function __construct(
        Practice $practice,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($practice, $start, $end);
        $this->service = new StatsHelper(new PracticeReportable($practice));
        $this->clhpppm = $this->for->clh_pppm ?? false;
    }

    public function render()
    {
        setlocale(LC_MONETARY, 'en_US.UTF-8');
        $total = $this->service->totalBilled();
        $this->data['billed_so_far'] = $total;

        $this->data['revenue_so_far'] = money_format('%.0n', round($total * 40, -2));
        $this->data['profit_so_far'] = money_format('%.0n', $total * 40 - $total * $this->clhpppm);

        for ($i = 0; $i < 3; $i++) {
            if ($i == 0) {
                $start = Carbon::parse($this->end)->firstOfMonth();
                $month = Carbon::parse($this->end)->format('F Y');
            } else {
                $iMonthsAgo = Carbon::parse($this->end)->subMonths($i);

                $start = Carbon::parse($iMonthsAgo)->firstOfMonth();
                $month = Carbon::parse($iMonthsAgo)->format('F Y');
            }

            $billable = $this->service->billableCountForMonth($start);
            $billableDollars = $billable * 40;
            $billableRounded = $billableDollars;


            if ($billableDollars == 0) {
                $profit = 0;
            } else {
                $profit = ($billableRounded * (1 - ($this->clhpppm / 40)));
                $profit = floor($profit / 10) * 10;
            }

            $this->data['historical']['Patients >20mins (some are not billed)'][$month]
                = $billable;

            $this->data['historical']['CCM Profit (Approx.)'][$month]
                = ($this->clhpppm != 0)
                ? money_format('%.0n', round($profit, 0))
                : 'N/A';
        }

        return $this->data;
    }
}
