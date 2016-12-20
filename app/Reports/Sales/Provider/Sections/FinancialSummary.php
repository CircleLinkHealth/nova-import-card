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

    public function __construct(
        User $provider,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($provider, $start, $end);
        $this->provider = $provider;
        $this->service = (new ProviderStatsHelper($start, $end));
    }

    public function renderSection()
    {



    }

}