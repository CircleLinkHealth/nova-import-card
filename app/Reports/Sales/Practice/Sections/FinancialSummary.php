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

        $total = $this->service->totalBilled($this->practice);
        $this->data['billed_so_far'] = $total;


        $this->data['revenue_so_far'] = '$' . round($total * 40, -2);
        $this->data['profit_so_far'] = '$' . ($total * 40 - $total * $this->clhpppm);

        for ($i = 1; $i < 5; $i++) {

            $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);

            $start = Carbon::parse($iMonthsAgo)->firstOfMonth();

            $month = Carbon::parse($iMonthsAgo)->format('F Y');

            $billable = $this->service->billableCountForMonth($this->practice, $start);
            $billableDollars = $billable * 40;
            $billableRounded = intval($billableDollars / 100) * 100;

            if($billableDollars == 0){
                $profit = 0;
            } else {
                $profit = ($billableRounded * ( 1 - ($this->clhpppm/40)));
            }


            $this->data['historical']['Billable'][$month]
                = $billable;

            $this->data['historical']['CCM Revenue'][$month]
                = '$' . $billableRounded;

            $this->data['historical']['CCM Profit'][$month]
                = ($this->clhpppm)
                ? '$' . round($profit, 0)
                : 'N/A';

        }

        return $this->data;

    }


}