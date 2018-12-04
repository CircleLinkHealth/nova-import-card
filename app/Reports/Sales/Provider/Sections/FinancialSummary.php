<?php

namespace App\Reports\Sales\Provider\Sections;

use App\Reports\Sales\ProviderReportable;
use App\Reports\Sales\SalesReportSection;
use App\Reports\Sales\StatsHelper;
use App\User;
use Carbon\Carbon;

class FinancialSummary extends SalesReportSection
{
    private $provider;
    private $service;
    private $clhpppm;

    public function __construct(
        User $provider,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($provider, $start, $end);
        $this->provider = $provider;
        $this->service = new StatsHelper(new ProviderReportable($provider));
        $this->clhpppm = $this->provider->primaryPractice->clh_pppm ?? false;
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
                $start      = Carbon::parse($iMonthsAgo)->firstOfMonth();
                $month      = Carbon::parse($iMonthsAgo)->format('F Y');
            }

            $billable = $this->service->billableCountForMonth($start);
            $billableDollars = $billable * 40;
            $billableRounded = intval($billableDollars / 10) * 10;

            if ($billableDollars == 0) {
                $profit = 0;
            } else {
                $profit = ($billableRounded * (1 - ($this->clhpppm / 40)));
                $profit = floor($profit / 10) * 10;
            }


            $this->data['historical']['Patients >20mins (some are not billed)'][$month]
                = $billable;

//            $this->data['historical']['CCM Revenue'][$month]
//                = '~'.money_format('%.0n',$billableRounded);

            $this->data['historical']['CCM Profit (Approx.)'][$month]
                = ($this->clhpppm != 0)
                ? $this->formatDollar(round($profit, 0))
                : 'N/A';
        }

        return $this->data;
    }

    public function formatDollar($amount)
    {
        return money_format('%.0n', $amount);
    }
}
