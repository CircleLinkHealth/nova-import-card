<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Support\Facades\DB;

class PracticeKPIs
{
    protected $acqCost;

    protected $consented;

    protected $conversion;
    /**
     * @var string
     */
    protected $end;

    protected $hardDeclined;

    protected $incompleteThreeAttempts;

    protected $laborHours;

    protected $laborRate;
    /**
     * @var Practice
     */
    protected $practice;

    protected $practiceEnrollees;

    protected $softDeclined;

    /**
     * @var string
     */
    protected $start;

    protected $totalCost;

    protected $totalTime;

    protected $uniquePatientsCalled;

    protected $utc;

    public function __construct(Practice $practice, string $start, string $end)
    {
        $this->practice = $practice;
        $this->start    = $start;
        $this->end      = $end;
    }

    public static function get(Practice $practice, string $start, string $end)
    {
        return (new static($practice, $start, $end))->makeStats();
    }

    private function formatAndGetTotalCost()
    {
        return '$'.number_format($this->totalCost, 2);
    }

    private function makeStats(): array
    {
        return $this->setPracticeEnrolleeData()
            ->setUniquePatientsCalled()
            ->setConsented()
            ->setUtc()
            ->setSoftDeclined()
            ->setHardDeclined()
            ->setIncompleteThreeAttempts()
            ->setTotalTime()
            ->setLaborHours()
            ->setConversion()
            ->setTotalCost()
            ->setLaborRate()
            ->setAcqCost()
            ->toArray();
    }

    private function setAcqCost()
    {
        $totalCostAndConsentedExist = $this->totalCost > 0 && $this->consented > 0;
        $this->acqCost              = $totalCostAndConsentedExist ?
            '$'.number_format(
                $this->totalCost / $this->consented,
                2
            )
       : 'N/A';

        return $this;
    }

    private function setConsented()
    {
        $this->consented = $this->practiceEnrollees->whereIn('enrollee_status', [
            Enrollee::CONSENTED,
            Enrollee::ENROLLED,
        ])
            ->count();

        return $this;
    }

    private function setConversion()
    {
        $consentedExist = $this->uniquePatientsCalled > 0 && $this->consented > 0;

        $this->conversion = $consentedExist ?
             number_format(
                 $this->consented / $this->uniquePatientsCalled * 100,
                 2
             ).'%'

            : 'N/A';

        return $this;
    }

    private function setHardDeclined()
    {
        $this->hardDeclined = $this->practiceEnrollees->where('enrollee_status', Enrollee::REJECTED)
            ->count();

        return $this;
    }

    private function setIncompleteThreeAttempts()
    {
        $this->incompleteThreeAttempts = $this->practiceEnrollees->where('enrollee_attempt_count', '>=', 3)
            ->whereNotIn(
                'enrollee_status',
                [Enrollee::UNREACHABLE, Enrollee::SOFT_REJECTED, Enrollee::REJECTED]
            )->count();

        return $this;
    }

    private function setLaborHours()
    {
        $this->laborHours = secondsToHMS($this->totalTime);

        return $this;
    }

    private function setLaborRate()
    {
        $totalTimeAndCostExist = $this->totalTime > 0 && $this->totalCost > 0;
        $this->laborRate       = $totalTimeAndCostExist ?
             '$'.number_format(
                 $this->totalCost / ($this->totalTime / 3600),
                 2
             )
       :
           'N/A';

        return $this;
    }

    private function setPracticeEnrolleeData()
    {
        $this->practiceEnrollees = PageTimer::select(
            DB::raw('lv_page_timer.provider_id as ca_user_id'),
            DB::raw('enrollees.practice_id'),
            'enrollee_id',
            DB::raw('enrollees.status as enrollee_status'),
            'start_time',
            'end_time',
            DB::raw('SUM(billable_duration) as total_time'),
            DB::raw('enrollees.attempt_count as enrollee_attempt_count'),
            DB::raw('care_ambassadors.hourly_rate as ca_hourly_rate'),
            DB::raw('(SUM(billable_duration)/3600) * care_ambassadors.hourly_rate as cost')
        )
            ->rightJoin('enrollees', 'lv_page_timer.enrollee_id', '=', 'enrollees.id')
            ->leftJoin('care_ambassadors', 'lv_page_timer.provider_id', '=', 'care_ambassadors.user_id')
            ->whereNotNull('lv_page_timer.provider_id')
            ->whereNotNull('enrollee_id')
            ->where('enrollees.practice_id', $this->practice->id)
            ->where('start_time', '>=', $this->start)
            ->where('end_time', '<=', $this->end)
            ->groupBy('enrollee_id')
            ->get();

        return $this;
    }

    private function setSoftDeclined()
    {
        $this->softDeclined = $this->practiceEnrollees->where('enrollee_status', Enrollee::SOFT_REJECTED)
            ->count();

        return $this;
    }

    private function setTotalCost()
    {
        $this->totalCost = $this->practiceEnrollees->sum('cost');

        return $this;
    }

    private function setTotalTime()
    {
        $this->totalTime = $this->practiceEnrollees->sum('total_time');

        return $this;
    }

    private function setUniquePatientsCalled()
    {
        $this->uniquePatientsCalled = $this->practiceEnrollees->whereIn('enrollee_status', [
            Enrollee::CONSENTED,
            Enrollee::ENROLLED,
            Enrollee::UNREACHABLE,
            Enrollee::REJECTED,
            Enrollee::SOFT_REJECTED,
        ])
            ->count();

        return $this;
    }

    private function setUtc()
    {
        $this->utc = $this->practiceEnrollees->where('enrollee_status', Enrollee::UNREACHABLE)
            ->count();

        return $this;
    }

    private function toArray()
    {
        return [
            'name'                   => $this->practice->display_name,
            'unique_patients_called' => $this->uniquePatientsCalled,
            'consented'              => $this->consented,
            'utc'                    => $this->utc,
            'soft_declined'          => $this->softDeclined,
            'hard_declined'          => $this->hardDeclined,
            'incomplete_3_attempts'  => $this->incompleteThreeAttempts,
            'labor_hours'            => $this->laborHours,
            'conversion'             => $this->conversion,
            'labor_rate'             => $this->laborRate,
            'acq_cost'               => $this->acqCost,
            'total_cost'             => $this->formatAndGetTotalCost(),
        ];
    }
}
