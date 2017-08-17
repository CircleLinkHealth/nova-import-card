<?php

namespace App\Reports\Sales;


use App\Activity;
use App\Call;
use App\CarePerson;
use App\Contracts\Reports\Reportable;
use App\MailLog;
use App\Observation;
use App\PatientMonthlySummary;
use App\User;
use Carbon\Carbon;

class ProviderReportable implements Reportable
{
    protected $provider;

    public function __construct(User $provider)
    {
        $this->provider = $provider;
    }

    /**
     * All patients for this Reportable.
     *
     * @return mixed
     */
    public function patients()
    {
        return User::ofType('participant')
            ->whereHas('careTeamMembers', function ($q) {
                $q->whereType(CarePerson::BILLING_PROVIDER)
                    ->whereMemberUserId($this->provider->id);
            })->get();
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
            $q->hasBillingProvider($this->provider->id);
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
        return Activity
            ::whereHas('patient', function ($q) {
                $q->hasBillingProvider($this->provider->id);
            })
            ->where('created_at', '>=', $start->toDateTimeString())
            ->where('created_at', '<=', $end->toDateTimeString())
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
        return Observation
            ::whereHas('user', function ($q) {
                $q->hasBillingProvider($this->provider->id);
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
        return MailLog::whereReceiverCpmId($this->provider->id)
            ->whereNotNull('note_id')
            ->where('created_at', '>', $start->toDateTimeString())
            ->where('created_at', '<', $end->toDateTimeString())
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
        return MailLog
            ::whereHas('note', function ($q) {
                $q->where('isTCM', 1);
            })
            ->whereReceiverCpmId($this->provider->id)
            ->whereNotNull('note_id')
            ->where('created_at', '>', $start)
            ->where('created_at', '<', $end)
            ->count();
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
                $k->whereHas('careTeamMembers', function ($q) {
                    $q->whereType(CarePerson::BILLING_PROVIDER)
                        ->whereMemberUserId($this->provider->id);
                });
            });
        })
            ->where('ccm_time', '>', 1199);

        if ($month) {
            $q->where('month_year', $month->firstOfMonth());
        }

        return $q->count();
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
     * The link to view this Reportable's notes.
     *
     * @return mixed
     */
    public function linkToNotes()
    {
        return route('patient.note.listing') . "/?provider={$this->provider->id}";
    }
}