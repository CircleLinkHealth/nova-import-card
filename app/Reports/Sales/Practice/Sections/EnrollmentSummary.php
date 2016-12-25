<?php

namespace App\Reports\Sales\Practice\Sections;

use App\PatientInfo;
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
        $this->service = (new PracticeStatsHelper($start, $end));
        $this->clhpppm = $this->practice->clh_pppm ?? false;
    }

    public function renderSection()
    {
        $id = $this->practice->id;

        $enrollmentCumulative = PatientInfo::whereHas('user', function ($q) use ($id) {

            $q->whereProgramId($id);

        })
            ->whereNotNull('ccm_status')
            ->select(DB::raw('count(ccm_status) as total, ccm_status'))
            ->groupBy('ccm_status')
            ->get()
            ->toArray();

        $this->data['enrolled'] = $enrollmentCumulative[0]['total'] ?? 0;
        $this->data['paused'] = $enrollmentCumulative[1]['total'] ?? 0;
        $this->data['withdrawn'] = $enrollmentCumulative[2]['total'] ?? 0;

        $this->data['historical'] = $this->service->historicalEnrollmentPerformance($this->practice, Carbon::parse($this->start), Carbon::parse($this->end));

        return $this->data;

    }


}