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

        for ($i = 0; $i < 4; $i++) {

            $billable = $this->service->billableCountForMonth($this->practice, Carbon::parse($this->start)->subMonths($i));

            //if first month, do a month-to-date
            if ($i == 0) {

                $month = Carbon::parse($this->start)->format('F Y');
                $this->data['Enrollment Summary'][$month] = $this->service->enrollmentCountByPractice($this->practice,
                    $this->start, $this->end);

                $this->data[$month]['billable'] = $billable;

            } else {

                $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);
                $start = Carbon::parse($iMonthsAgo)->firstOfMonth();
                $end = Carbon::parse($iMonthsAgo)->lastOfMonth();

                $month = Carbon::parse($iMonthsAgo)->format('F Y');
                $this->data[$month] = $this->service->enrollmentCountByPractice($this->practice,
                    $start, $end);

                $this->data[$month]['billable'] = $billable;
            }

        }

        return $this->data;

    }


}