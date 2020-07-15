<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\Customer\Entities\CareAmbassador;
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
        $data = [];

        $data['name'] = $this->practice->display_name;

        $data['unique_patients_called'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            //status are needed here for the sake of end-user seeing numbers. E.g a stat is not shown for an ineligible patient, so don't count in totals
            ->whereIn('status', [
                Enrollee::UNREACHABLE,
                Enrollee::CONSENTED,
                Enrollee::ENROLLED,
                Enrollee::REJECTED,
                Enrollee::SOFT_REJECTED,
            ])
            ->count();

        $data['consented'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->whereIn('status', [Enrollee::CONSENTED, Enrollee::ENROLLED])
            ->count();

        $data['utc'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->where('status', Enrollee::UNREACHABLE)
            ->count();

        $data['hard_declined'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->where('status', Enrollee::REJECTED)
            ->count();

        $data['soft_declined'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->where('status', Enrollee::SOFT_REJECTED)
            ->count();

        $total_time = Enrollee
                ::where('practice_id', $this->practice->id)
                    ->where('last_attempt_at', '>=', $this->start)
                    ->where('last_attempt_at', '<=', $this->end)
                    ->sum('total_time_spent');

        $data['labor_hours'] = secondsToHMS($total_time);

        $data['incomplete_3_attempts'] = Enrollee
                ::where('practice_id', $this->practice->id)
                    ->where('last_attempt_at', '>=', $this->start)
                    ->where('last_attempt_at', '<=', $this->end)
                    ->where('attempt_count', '>=', 3)
                    ->whereNotIn(
                        'status',
                        [Enrollee::UNREACHABLE, Enrollee::SOFT_REJECTED, Enrollee::REJECTED]
                    )
                    ->count();

        $enrollers = Enrollee::select(DB::raw('care_ambassador_user_id, sum(total_time_spent) as total'))
            ->where('practice_id', $this->practice->id)
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->groupBy('care_ambassador_user_id')
            ->pluck('total', 'care_ambassador_user_id');

        $data['total_cost'] = 0;

        foreach ($enrollers as $enrollerId => $time) {
            if (empty($enrollerId)) {
                continue;
            }

            $enroller = CareAmbassador::where('user_id', $enrollerId)->first();
            if ( ! $enroller) {
                continue;
            }
            $data['total_cost'] += $enroller->hourly_rate * $time / 3600;
        }

        if ($data['unique_patients_called'] > 0 && $data['consented'] > 0) {
            $data['conversion'] = number_format(
                $data['consented'] / $data['unique_patients_called'] * 100,
                2
            ).'%';
        } else {
            $data['conversion'] = 'N/A';
        }

        if ($data['total_cost'] > 0 && $data['consented'] > 0) {
            $data['acq_cost'] = '$'.number_format(
                $data['total_cost'] / $data['consented'],
                2
            );
        } else {
            $data['acq_cost'] = 'N/A';
        }

        if ($data['total_cost'] > 0 && $total_time > 0) {
            $data['labor_rate'] = '$'.number_format(
                $data['total_cost'] / ($total_time / 3600),
                2
            );
        } else {
            $data['labor_rate'] = 'N/A';
        }

        $data['total_cost'] = '$'.number_format($data['total_cost'], 2);

        return $data;
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
        $this->consented = $this->practiceEnrollees->whereIn('status', [
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
        $this->hardDeclined = $this->practiceEnrollees->where('status', Enrollee::REJECTED)
            ->count();

        return $this;
    }

    private function setIncompleteThreeAttempts()
    {
        $this->incompleteThreeAttempts = $this->practiceEnrollees->where('enrollee_attempt_count', '>=', 3)
            ->whereNotIn(
                'status',
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
        $this->softDeclined = $this->practiceEnrollees->where('status', Enrollee::SOFT_REJECTED)
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
        $this->uniquePatientsCalled = $this->practiceEnrollees
            ->count();

        return $this;
    }

    private function setUtc()
    {
        $this->utc = $this->practiceEnrollees->where('status', Enrollee::UNREACHABLE)
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
