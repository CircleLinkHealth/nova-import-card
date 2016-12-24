<?php

namespace App\Reports\Sales\Practice\Sections;

use App\Practice;
use App\Reports\Sales\Practice\PracticeStatsHelper;
use App\Reports\Sales\SalesReportSection;
use Carbon\Carbon;

class FinancialSummary extends SalesReportSection
{

    private $practice;
    private $clhpppm;
    private $service;

    public function __construct(
        Practice $practice,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($practice, $start, $end);
        $this->practice = $practice;
        $this->service = (new PracticeStatsHelper($start, $end));
        $this->clhpppm = $this->practice->clh_pppm ?? false;
    }

    public function renderSection()
    {

        $total =  $this->service->totalBilled($this->practice);
        $this->data['billed_so_far'] = $total;

        $this->data['revenue_so_far'] = '$'.round($total * 40, -2);
        $this->data['profit_so_far']  = '$'.($total * 40 - $total * $this->clhpppm);

        for ($i = 1; $i < 5; $i++) {

            $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);

            $start = Carbon::parse($iMonthsAgo)->firstOfMonth();

            $month = Carbon::parse($iMonthsAgo)->format('F Y');

            $billable = $this->service->billableCountForMonth($this->practice, $start);

            $this->data['historical']['Billable'][$month]
                = $billable;

            $this->data['historical']['CCM Revenue'][$month]
                = '$' . round($billable * 40, -2);

            $this->data['historical']['CCM Profit'][$month]
                = ($this->clhpppm) ? '$' . ($billable * 40 - $billable * $this->clhpppm) : 'N/A';

        }

        return $this->data;

    }


}