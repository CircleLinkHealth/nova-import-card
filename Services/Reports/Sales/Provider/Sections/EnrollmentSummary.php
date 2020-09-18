<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Provider\Sections;

use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\ProviderReportable;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\SalesReportSection;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\StatsHelper;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\DB;

class EnrollmentSummary extends SalesReportSection
{
    protected $service;

    public function __construct(
        User $provider,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($provider, $start, $end);
        $this->provider = $provider;
        $this->service  = new StatsHelper(new ProviderReportable($provider));
    }

    public function render()
    {
        $enrollmentCumulative = Patient::whereHas('user', function ($q) {
            $q->hasBillingProvider($this->for->id);
        })
            ->whereNotNull('ccm_status')
            ->select(DB::raw('count(ccm_status) as total, ccm_status'))
            ->groupBy('ccm_status')
            ->get()
            ->toArray();

        $this->data['enrolled'] = $enrollmentCumulative[0]['total'] ?? 0;
//        $this->data['paused'] = $enrollmentCumulative[1]['total'] ?? 0;
//        $this->data['withdrawn'] = $enrollmentCumulative[2]['total'] ?? 0;

        $this->data['historical'] = $this->service->historicalEnrollmentPerformance($this->start->startOfMonth(), $this->end);

        return $this->data;
    }
}
