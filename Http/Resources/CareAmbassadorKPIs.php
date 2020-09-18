<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Resources;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CareAmbassadorKPIs
{
    protected $callsPerHour;

    protected $careAmbassadorModel;
    /**
     * @var User
     */
    protected $careAmbassadorUser;

    protected $conversion;

    protected $earnings;

    /**
     * @var string
     */
    protected $end;

    protected $enrolleesAssigned;

    protected $hourlyRate;

    protected $minsPerEnrollment;

    protected $perCost;

    /**
     * @var string
     */
    protected $start;

    protected $totalCalled;

    protected $totalEnrolled;

    protected $totalHours;

    protected $totalSeconds;

    public function __construct(User $careAmbassadorUser, Carbon $start, Carbon $end)
    {
        $this->careAmbassadorUser = $careAmbassadorUser;
        $this->start              = $start;
        $this->end                = $end;
    }

    public static function get(User $careAmbassadorUser, Carbon $start, Carbon $end)
    {
        return (new static($careAmbassadorUser, $start, $end))->makeStats();
    }

    private function hourlyRateIsSet()
    {
        return 'Not Set' != $this->hourlyRate;
    }

    private function makeStats(): array
    {
        $this->careAmbassadorModel = $this->careAmbassadorUser->careAmbassador;

        if ( ! $this->careAmbassadorModel) {
            Log::critical("No CareAmbassador Model found for CA User with id: {$this->careAmbassadorUser->id}");

            //should never be the case but even if it is, this is non breaking
            return [];
        }

        return $this->setCareAmbassadorAssignedEnrollees()
            ->setHourlyRate()
            ->setTotalSeconds()
            ->setTotalHours()
            ->setTotalEnrolled()
            ->setTotalCalled()
            ->setMinsPerEnrollment()
            ->setEarnings()
            ->setCallsPerHour()
            ->setConversion()
            ->setPerCost()
            ->toArray();
    }

    private function setCallsPerHour()
    {
        $this->callsPerHour = 0 != $this->totalSeconds ? number_format(
            $this->totalCalled / ($this->totalSeconds / 3600),
            2
        ) : 'N/A';

        return $this;
    }

    private function setCareAmbassadorAssignedEnrollees()
    {
        $this->enrolleesAssigned = PageTimer::select(
            DB::raw('lv_page_timer.provider_id as ca_user_id'),
            'enrollee_id',
            DB::raw('enrollees.status as enrollee_status'),
            DB::raw('enrollees.care_ambassador_user_id as last_assigned_care_ambassador_user_id'),
            'start_time',
            'end_time',
            DB::raw('SUM(duration) as total_time')
        )
            ->leftJoin('enrollees', 'lv_page_timer.enrollee_id', '=', 'enrollees.id')
            ->where('lv_page_timer.provider_id', $this->careAmbassadorUser->id)
            ->whereNotNull('enrollee_id')
            ->where('start_time', '>=', $this->start)
            ->where('end_time', '<=', $this->end)
            ->groupBy('enrollee_id')
            ->get();

        return $this;
    }

    private function setConversion()
    {
        $this->conversion = 0 != $this->totalCalled ? number_format(
            ($this->totalEnrolled / $this->totalCalled) * 100,
            2
        ).'%' : 'N/A';

        return $this;
    }

    private function setEarnings()
    {
        $this->earnings = $this->hourlyRateIsSet() ? '$'.number_format(
            $this->hourlyRate * ($this->totalSeconds / 3600),
            2
        ) : 'N/A';

        return $this;
    }

    private function setHourlyRate()
    {
        $this->hourlyRate = $this->careAmbassadorModel->hourly_rate ?? 'Not Set';

        return $this;
    }

    private function setMinsPerEnrollment()
    {
        $this->minsPerEnrollment = (0 != $this->totalEnrolled)
            ?
            number_format(($this->totalSeconds / 60) / $this->totalEnrolled, 2)
            : 0;

        return $this;
    }

    private function setPerCost()
    {
        $this->perCost = $this->hourlyRateIsSet() && 0 != $this->totalEnrolled
            ? '$'.number_format(
                (($this->totalSeconds / 3600) * $this->hourlyRate) / $this->totalEnrolled,
                2
            ) : 'N/A';

        return $this;
    }

    private function setTotalCalled()
    {
        $this->totalCalled = $this->enrolleesAssigned
            ->where('last_assigned_care_ambassador_user_id', $this->careAmbassadorUser->id)
            ->whereIn('enrollee_status', [
                Enrollee::CONSENTED,
                Enrollee::ENROLLED,
                Enrollee::UNREACHABLE,
                Enrollee::REJECTED,
                Enrollee::SOFT_REJECTED,
            ])->count();

        return $this;
    }

    private function setTotalEnrolled()
    {
        $this->totalEnrolled = $this->enrolleesAssigned
            ->where('last_assigned_care_ambassador_user_id', $this->careAmbassadorUser->id)
            ->whereIn('enrollee_status', [Enrollee::CONSENTED, Enrollee::ENROLLED])
            ->count();

        return $this;
    }

    private function setTotalHours()
    {
        $this->totalHours = floatval(round($this->totalSeconds / 3600, 2));

        return $this;
    }

    private function setTotalSeconds()
    {
        $this->totalSeconds = $this->enrolleesAssigned->sum('total_time');

        return $this;
    }

    private function toArray()
    {
        return [
            'name'                => $this->careAmbassadorUser->getFullName(),
            'total_hours'         => $this->totalHours,
            'total_seconds'       => $this->totalSeconds,
            'no_enrolled'         => $this->totalEnrolled,
            'total_calls'         => $this->totalCalled,
            'calls_per_hour'      => $this->callsPerHour,
            'mins_per_enrollment' => $this->minsPerEnrollment,
            'earnings'            => $this->earnings,
            'conversion'          => $this->conversion,
            'hourly_rate'         => $this->hourlyRate,
            'per_cost'            => $this->perCost,
            'total_cost'          => $this->hourlyRateIsSet() ? $this->hourlyRate * ($this->totalSeconds / 3600) : 0,
        ];
    }
}
