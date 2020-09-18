<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Practice\Sections;

use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\PracticeReportable;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\SalesReportSection;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\StatsHelper;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Support\Facades\DB;

class EnrollmentSummary extends SalesReportSection
{
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
        $enrollmentCumulative = Patient::whereHas('user', function ($q) {
            $q->whereProgramId($this->for->id);
        })
            ->whereNotNull('ccm_status')
            ->select(DB::raw('count(ccm_status) as total, ccm_status'))
            ->groupBy('ccm_status')
            ->get()
            ->toArray();

        $this->data['enrolled'] = $enrollmentCumulative[0]['total'] ?? 'N/A';
//        $this->data['paused'] = $enrollmentCumulative[1]['total'] ?? 'N/A';
//        $this->data['withdrawn'] = $enrollmentCumulative[2]['total'] ?? 'N/A';

        $this->data['historical'] = $this->service->historicalEnrollmentPerformance($this->start->startOfMonth(), $this->end);

        return $this->data;
    }
}
