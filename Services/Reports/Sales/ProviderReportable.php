<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales;

use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Contracts\Reportable;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\SharedModels\Entities\Observation;
use CircleLinkHealth\SharedModels\Entities\Activity;

class ProviderReportable implements Reportable
{
    protected $provider;

    public function __construct(User $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Sum of activity time for this Reportable.
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
     * Forwarded emergency notes count for this Reportable.
     *
     * @return mixed
     */
    public function forwardedEmergencyNotesCount(Carbon $start, Carbon $end)
    {
        return Note::forwardedTo(get_class($this->provider), $this->provider->id, $start, $end)
            ->emergency()
            ->count();
    }

    /**
     * Forwarded notes count for this Reportable.
     *
     * @return mixed
     */
    public function forwardedNotesCount(Carbon $start, Carbon $end)
    {
        return Note::forwardedTo(get_class($this->provider), $this->provider->id, $start, $end)
            ->count();
    }

    /**
     * The link to view this Reportable's notes.
     *
     * @return mixed
     */
    public function linkToNotes()
    {
        return route('patient.note.listing')."/?provider={$this->provider->id}";
    }

    /**
     * Observation count for this Reportable.
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
     * All patients for this Reportable.
     *
     * @return mixed
     */
    public function patients()
    {
        return User::ofType('participant')
            ->hasBillingProvider($this->provider->id)
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
            $q->whereHas('careTeamMembers', function ($q) {
                $q->whereType(CarePerson::BILLING_PROVIDER)
                    ->whereMemberUserId($this->provider->id);
            });
        })
            ->where('total_time', '>', 1199);

        if ($month) {
            $q->where('month_year', $month->firstOfMonth());
        }

        return $q->count();
    }
}
