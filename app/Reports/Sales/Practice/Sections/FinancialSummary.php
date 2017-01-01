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
        setlocale(LC_MONETARY, 'en_US.UTF-8');
        $total = $this->service->totalBilled($this->practice);
        $this->data['billed_so_far'] = $total;

        $this->data['revenue_so_far'] = money_format('%.0n', round($total * 40, -2));
        $this->data['profit_so_far'] = money_format('%.0n', $total * 40 - $total * $this->clhpppm);

        for ($i = 0; $i < 3; $i++) {

            if($i == 0){

                $start = Carbon::parse($this->start)->firstOfMonth();
                $month = Carbon::parse($this->start)->format('F Y');

            } else {

                $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);

                $start = Carbon::parse($iMonthsAgo)->firstOfMonth();
                $month = Carbon::parse($iMonthsAgo)->format('F Y');

            }

            $billable = $this->service->billableCountForMonth($this->practice, $start);
            $billableDollars = $billable * 40;
            $billableRounded = intval($billableDollars / 100) * 100;

            if($billableDollars == 0){
                $profit = 0;
            } else {
                $profit = ($billableRounded * ( 1 - ($this->clhpppm/40)));
            }


            $this->data['historical']['Patients >20mins (some are not billed)'][$month]
                = $billable;

//            $this->data['historical']['CCM Revenue'][$month]
//                = '~'.money_format('%.0n',$billableRounded);

            $this->data['historical']['CCM Profit'][$month]
                = ($this->clhpppm != 0)
                ? '~'.money_format('%.0n',round($profit, 0))
                : 'N/A';

        }

        return $this->data;

    }


}