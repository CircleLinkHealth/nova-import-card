<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use App\CareAmbassadorLog;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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

    protected $patientEarnings;

    protected $patientHours;

    protected $patientSeconds;

    protected $perCost;

    protected $shouldSetCostRelatedMetrics;

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
            ->setPatientSeconds()
            ->setPatientHours()
            ->setTotalEnrolled()
            ->setTotalCalled()
            ->setMinsPerEnrollment()
            ->setEarnings()
            ->setCallsPerHour()
            ->setConversion()
            ->setPerCost()
            ->setPatientEarnings()
            ->toArray();
    }

    private function setCallsPerHour()
    {
        $this->callsPerHour = $this->shouldSetCostRelatedMetrics() ? number_format(
            $this->totalCalled / ($this->totalSeconds / 3600),
            2
        ) : 'N/A';

        return $this;
    }

    private function setCareAmbassadorAssignedEnrollees()
    {
        $this->enrolleesAssigned = Enrollee::select('id', 'status', 'total_time_spent')
            ->where('care_ambassador_user_id', $this->careAmbassadorUser->id)
            ->ofStatus([
                Enrollee::UNREACHABLE,
                Enrollee::CONSENTED,
                Enrollee::ENROLLED,
                Enrollee::REJECTED,
                Enrollee::SOFT_REJECTED,
            ])
            ->lastCalledBetween($this->start, $this->end)
            ->get();

        return $this;
    }

    private function setConversion()
    {
        $this->conversion = $this->shouldSetCostRelatedMetrics() ? number_format(
            ($this->totalEnrolled / $this->totalCalled) * 100,
            2
        ).'%' : 'N/A';

        return $this;
    }

    private function setEarnings()
    {
        $this->earnings = $this->shouldSetCostRelatedMetrics() ? '$'.number_format(
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

    private function setPatientEarnings()
    {
        $this->patientEarnings = $this->shouldSetCostRelatedMetrics() ? '$'.number_format($this->hourlyRate * ($this->patientSeconds / 3600), 2) : 'N/A';

        return $this;
    }

    private function setPatientHours()
    {
        $this->patientHours = floatval(round($this->patientSeconds / 3600, 2));

        return $this;
    }

    private function setPatientSeconds()
    {
        $this->patientSeconds = $this->enrolleesAssigned->sum('total_time_spent');

        return $this;
    }

    private function setPerCost()
    {
        $this->perCost = $this->shouldSetCostRelatedMetrics() ? '$'.number_format(
            (($this->totalSeconds / 3600) * $this->hourlyRate) / $this->totalEnrolled,
            2
        ) : 'N/A';

        return $this;
    }

    private function setTotalCalled()
    {
        $this->totalCalled = $this->enrolleesAssigned->count();

        return $this;
    }

    private function setTotalEnrolled()
    {
        $this->totalEnrolled = $this->enrolleesAssigned
            ->whereIn('status', [Enrollee::CONSENTED, Enrollee::ENROLLED])
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
        $this->totalSeconds = CareAmbassadorLog::where('enroller_id', $this->careAmbassadorModel->id)
            ->where('day', '>=', $this->start)
            ->where('day', '<=', $this->end)
            ->sum('total_time_in_system');

        return $this;
    }

    private function shouldSetCostRelatedMetrics()
    {
        if (null === $this->shouldSetCostRelatedMetrics) {
            $this->shouldSetCostRelatedMetrics = 0 != $this->totalCalled
                && 0 != $this->totalEnrolled
                && 'Not Set' != $this->hourlyRate
                && 0 !== $this->totalSeconds;
        }

        return $this->shouldSetCostRelatedMetrics;
    }

    private function toArray()
    {
        return [
            'name'                => $this->careAmbassadorUser->getFullName(),
            'total_hours'         => $this->totalHours,
            'total_seconds'       => $this->totalSeconds,
            'patient_hours'       => $this->patientHours,
            'patient_seconds'     => $this->patientSeconds,
            'no_enrolled'         => $this->totalEnrolled,
            'total_calls'         => $this->totalCalled,
            'calls_per_hour'      => $this->callsPerHour,
            'mins_per_enrollment' => $this->minsPerEnrollment,
            'earnings'            => $this->earnings,
            'conversion'          => $this->conversion,
            'hourly_rate'         => $this->hourlyRate,
            'patient_earnings'    => $this->patientEarnings,
            'per_cost'            => $this->perCost,
        ];
    }
}
