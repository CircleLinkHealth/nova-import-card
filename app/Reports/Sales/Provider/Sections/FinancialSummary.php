<?php

namespace App\Reports\Sales\Provider\Sections;

use App\Reports\Sales\Provider\ProviderStatsHelper;
use App\Reports\Sales\SalesReportSection;
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
        $this->service = (new ProviderStatsHelper($start, $end));
        $this->clhpppm = $this->provider->primaryPractice->clh_pppm;
    }

    public function renderSection()
    {

        $total =  $this->service->totalBilled($this->provider);
        $this->data['billed_so_far'] = $total;

        $this->data['revenue_so_far'] = '$'.round($total * 40, -2);
        $this->data['profit_so_far']  = '$'.($total * 40 - $total * $this->clhpppm);

        for ($i = 1; $i < 5; $i++) {

            $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);

            $start = Carbon::parse($iMonthsAgo)->firstOfMonth();

            $month = Carbon::parse($iMonthsAgo)->format('F Y');

            $billable = $this->service->billableCountForMonth($this->provider, $start);

            $this->data[$month]['Billable']
                = $billable;

            $this->data[$month]['CCM Revenue']
                = '$' . round($billable * 40, -2);

            $this->data[$month]['CCM Profit']
                = '$' . ($billable * 40 - $billable * $this->clhpppm);

        }

        return $this->data;

    }

}