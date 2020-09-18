<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales;

use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Contracts\Reportable;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\SharedModels\Entities\Observation;
use CircleLinkHealth\TimeTracking\Entities\Activity;

class PracticeReportable implements Reportable
{
    protected $practice;

    public function __construct(Practice $practice)
    {
        $this->practice = $practice;
    }

    /**
     * Sum of activity time for this Reportable.
     *
     * @return mixed
     */
    public function activitiesDuration(Carbon $start, Carbon $end)
    {
        return Activity::whereHas('patient', function ($q) {
            $q->where('program_id', '=', $this->practice->id);
        })
            ->where('performed_at', '>=', $start->toDateTimeString())
            ->where('performed_at', '<=', $end->toDateTimeString())
            ->sum('duration');
    }

    /**
     * Total eligible-to-be-billed patients count (for given month) for this Reportable.
     *
     * @return mixed
     */
    public function billablePatientsCountForMonth(Carbon $month)
    {
        return $this->totalBilledPatientsCount($month);
    }

    /**
     * Call count for this Reportable.
     *
     * @param null $status
     *
     * @return mixed
     */
    public function callCount(Carbon $start, Carbon $end, $status = null)
    {
        $q = Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', 'call')
                ->orWhere('sub_type', '=', 'Call Back');
        })->whereHas('inboundUser', function ($q) {
            $q->where('program_id', '=', $this->practice->id);
        })
            ->where('called_date', '>=', $start)
            ->where('called_date', '<=', $end);

        if ($status) {
            $q->whereStatus($status);
        }

        return $q->count();
    }

    /**
     * Forwarded emergency notes count for this Reportable.
     *
     * @return mixed
     */
    public function forwardedEmergencyNotesCount(Carbon $start, Carbon $end)
    {
        return Note::emergency()
            ->patientPractice($this->practice->id)
            ->forwarded($start, $end)
            ->count();
    }

    /**
     * Forwarded notes count for this Reportable.
     *
     * @return mixed
     */
    public function forwardedNotesCount(Carbon $start, Carbon $end)
    {
        return Note::forwarded($start, $end)
            ->patientPractice($this->practice->id)
            ->count();
    }

    /**
     * The link to view this Reportable's notes.
     *
     * @return mixed
     */
    public function linkToNotes()
    {
        return route('patient.note.listing');
    }

    /**
     * Observation count for this Reportable.
     *
     * @return mixed
     */
    public function observationsCount(Carbon $start, Carbon $end)
    {
        return Observation::whereHas('user', function ($q) {
            $q->whereProgramId($this->practice->id);
        })
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->count();
    }

    /**
     * All patients for this Reportable.
     *
     * @return mixed
     */
    public function patients()
    {
        return User::ofType('participant')
            ->where('program_id', '=', $this->practice->id)
            ->get();
    }

    /**
     * Total billed patients count (since the beginning of time) for this Reportable.
     *
     * @return mixed
     */
    public function totalBilledPatientsCount(Carbon $month = null)
    {
        $q = PatientMonthlySummary::whereHas('patient', function ($q) {
            $q->whereProgramId($this->practice->id);
        })
            ->where('total_time', '>', 1199);

        if ($month) {
            $q->where('month_year', $month->firstOfMonth());
        }

        return $q->count();
    }
}
