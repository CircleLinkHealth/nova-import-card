<?php

namespace App\Reports\Sales;

use App\Activity;
use App\Call;
use App\Contracts\Reports\Reportable;
use App\Observation;
use App\PatientMonthlySummary;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;

class PracticeReportable implements Reportable
{
    protected $practice;

    public function __construct(Practice $practice)
    {
        $this->practice = $practice;
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
     * Call count for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param null $status
     *
     * @return mixed
     */
    public function callCount(Carbon $start, Carbon $end, $status = null)
    {
        $q = Call::whereHas('inboundUser', function ($q) {
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
     * Sum of activity time for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
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
     * Observation count for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
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
     * Forwarded notes count for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return mixed
     */
    public function forwardedNotesCount(Carbon $start, Carbon $end)
    {
        return DatabaseNotification::distinct()
                                   ->whereHas('note.patient.primaryPractice', function ($q){
                                        $q->where('id', '=', $this->practice->id);
                                   })
                                   ->where('created_at', '>=', $start)
                                   ->where('created_at', '<=', $end)
                                   ->count();
    }

    /**
     * Forwarded emergency notes count for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return mixed
     */
    public function forwardedEmergencyNotesCount(Carbon $start, Carbon $end)
    {
        return DatabaseNotification::distinct()
                                   ->whereHas('note', function ($q) {
                                       $q->where('isTCM', 1)
                                         ->whereHas('patient', function ($k) {
                                             $k->where('program_id', '=', $this->practice->id);
                                         });
                                   })
                                   ->where('created_at', '>=', $start)
                                   ->where('created_at', '<=', $end)
                                   ->count(['attachment_id']);
    }

    /**
     * Total eligible-to-be-billed patients count (for given month) for this Reportable.
     *
     * @param Carbon $month
     *
     * @return mixed
     */
    public function billablePatientsCountForMonth(Carbon $month)
    {
        return $this->totalBilledPatientsCount($month);
    }

    /**
     * Total billed patients count (since the beginning of time) for this Reportable.
     *
     * @param Carbon|null $month
     *
     * @return mixed
     */
    public function totalBilledPatientsCount(Carbon $month = null)
    {
        $q = PatientMonthlySummary::whereHas('patient_info', function ($q) {
            $q->whereHas('user', function ($k) {
                $k->whereProgramId($this->practice->id);
            });
        })
                                  ->where('ccm_time', '>', 1199);

        if ($month) {
            $q->where('month_year', $month->firstOfMonth());
        }

        return $q->count();
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
}
