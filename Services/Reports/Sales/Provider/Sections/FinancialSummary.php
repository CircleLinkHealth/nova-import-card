<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Provider\Sections;

use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\ProviderReportable;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\SalesReportSection;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\StatsHelper;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;

class FinancialSummary extends SalesReportSection
{
    private $clhpppm;
    private $provider;
    private $service;

    public function __construct(
        User $provider,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($provider, $start, $end);
        $this->provider = $provider;
        $this->service  = new StatsHelper(new ProviderReportable($provider));
        $this->clhpppm  = $this->provider->primaryPractice->clh_pppm ?? false;
    }

    /**
     * @param $amount
     * @return bool|string
     */
    public function formatDollar($amount)
    {
        $formatter = new \NumberFormatter('us_US', \NumberFormatter::CURRENCY);

        return $formatter->format($amount);
    }

    public function render()
    {
        setlocale(LC_MONETARY, 'en_US.UTF-8');

        $total                        = $this->service->totalBilled();
        $this->data['billed_so_far']  = $total;
        $this->data['revenue_so_far'] = $this->formatDollar(round($total * 40, -2));
        $this->data['profit_so_far']  = $this->formatDollar($total * 40 - $total * $this->clhpppm);

        for ($i = 0; $i < 3; ++$i) {
            if (0 == $i) {
                $start = Carbon::parse($this->end)->firstOfMonth();
                $month = Carbon::parse($this->end)->format('F Y');
            } else {
                $iMonthsAgo = Carbon::parse($this->end)->subMonths($i);
                $start      = Carbon::parse($iMonthsAgo)->firstOfMonth();
                $month      = Carbon::parse($iMonthsAgo)->format('F Y');
            }

            $billable        = $this->service->billableCountForMonth($start);
            $billableDollars = $billable * 40;
            $billableRounded = intval($billableDollars / 10) * 10;

            if (0 == $billableDollars) {
                $profit = 0;
            } else {
                $profit = ($billableRounded * (1 - ($this->clhpppm / 40)));
                $profit = floor($profit / 10) * 10;
            }

            $this->data['historical']['Patients >20mins (some are not billed)'][$month]
                = $billable;

            $this->data['historical']['CCM Profit (Approx.)'][$month]
                = (0 != $this->clhpppm)
                ? $this->formatDollar(round($profit, 0))
                : 'N/A';
        }

        return $this->data;
    }
}
