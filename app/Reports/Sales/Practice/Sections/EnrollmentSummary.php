<?php

namespace App\Reports\Sales\Practice\Sections;

use App\Patient;
use App\Practice;
use App\Reports\Sales\Practice\PracticeStatsHelper;
use App\Reports\Sales\SalesReportSection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EnrollmentSummary extends SalesReportSection
{

    private $practice;
    private $service;


    public function __construct(
        Practice $practice,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($practice, $start, $end);
        $this->practice = $practice;
        $this->service = (new PracticeStatsHelper($practice, $start, $end));
        $this->clhpppm = $this->practice->clh_pppm ?? false;
    }

    public function renderSection()
    {
        $enrollmentCumulative = Patient::whereHas('user', function ($q) {
            $q->whereProgramId($this->practice->id);
        })
            ->whereNotNull('ccm_status')
            ->select(DB::raw('count(ccm_status) as total, ccm_status'))
            ->groupBy('ccm_status')
            ->get()
            ->toArray();

        $this->data['enrolled'] = $enrollmentCumulative[0]['total'] ?? 'N/A';
        $this->data['paused'] = $enrollmentCumulative[1]['total'] ?? 'N/A';
        $this->data['withdrawn'] = $enrollmentCumulative[2]['total'] ?? 'N/A';

        $this->data['historical'] = $this->service->historicalEnrollmentPerformance();

        return $this->data;

    }


}