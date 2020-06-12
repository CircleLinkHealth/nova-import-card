<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CareAmbassador;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

/**
 * Class EnrollableCallQueue.
 */
class EnrollableCallQueue
{
    const DAYS_FOR_NEXT_ATTEMPT = 3;

    /**
     * @var CareAmbassador
     */
    protected $careAmbassadorInfo;

    /**
     * Get all careAmbassador enrollees / do not prioritize speaks spanish.
     *
     * TOP - 1st PRIO - patients in qache (confirmed family members)
     * 2nd prio - Confirmed family members whom statuses have not been confirmed - edge case
     * 3rd prio - Patients who requested call today (or in the past days and they havent been called)
     * 4th prio - call queue, patients that haven't been called yet
     * 5th prio - utc patients where attempt count 1 && last attempt > 3 days ago
     * 6th prio - >> attempt count 2.
     *
     * Post conditions - never bring enrolled, consented, soft or hard decline, utc x3, ineligible, legacy
     * if patient is spanish and CA does not speak spanish, re-assign.
     *
     * @var array
     */
    protected $priority = [
        'getFromCache',
        'getPendingConfirmedFamilyMembers',
        'getRequestedCallbackToday',
        'getFromCallQueue',
        'getUtcAttemptCount',
    ];

    public function __construct(CareAmbassador $careAmbassadorInfo)
    {
        $this->careAmbassadorInfo = $careAmbassadorInfo;
    }

    public static function getCareAmbassadorPendingCallStatus(int $careAmbassadorUserId): array
    {
        $patientsPending = Enrollee::whereCareAmbassadorUserId($careAmbassadorUserId)
            ->lessThanThreeAttempts()
            ->where('status', Enrollee::UNREACHABLE)
            ->where('last_attempt_at', '>', Carbon::now()->startOfDay()->subDays(self::DAYS_FOR_NEXT_ATTEMPT))
            ->get();

        $patientsPendingCount = $patientsPending->count();
        $nextAttempt          = null;

        if (0 !== $patientsPendingCount) {
            $lastAttempt = $patientsPending
                ->sortBy('last_attempt_at')
                ->first()
                ->last_attempt_at;

            /**
             * @var Carbon
             */
            $nextAttempt = $lastAttempt->addDays(self::DAYS_FOR_NEXT_ATTEMPT);

            $patientWithRequestedCallback = $patientsPending->filter(function ($p) {
                return ! empty(trim($p->requested_callback));
            })
                ->sortBy('requested_callback')
                ->first();

            if ($patientWithRequestedCallback) {
                /**
                 * @var Carbon
                 */
                $callback = $patientWithRequestedCallback->requested_callback;

                if ($callback && $callback->lt($nextAttempt)) {
                    $nextAttempt = $callback;
                }
            }
        }

        return [
            'patients_pending' => $patientsPendingCount,
            'next_attempt_at'  => $nextAttempt ? $nextAttempt->toDateString() : null,
        ];
    }

    /**
     * @return Enrollee | null
     */
    public static function getNext(CareAmbassador $careAmbassadorInfo)
    {
        return (new static($careAmbassadorInfo))->retrieve();
    }

    /**
     * @param $confirmedFamilyMembers
     */
    public static function update(CareAmbassador $careAmbassador, Enrollee $enrollee, $confirmedFamilyMembers)
    {
        $queue = \Cache::has("care_ambassador_{$careAmbassador->id}_queue")
            ? \Cache::get("care_ambassador_{$careAmbassador->id}_queue")
            : [];

        $queue = collect(array_merge(
            $queue,
            explode(',', $confirmedFamilyMembers)
        ))->filter()->unique()->toArray();

        if ( ! empty($queue) && in_array($enrollee->id, $queue)) {
            unset($queue[array_search($enrollee->id, $queue)]);
        }

        \Cache::put("care_ambassador_{$careAmbassador->id}_queue", $queue, 600);
    }

    private function getFromCache(): ?Enrollee
    {
        $queue = \Cache::has("care_ambassador_{$this->careAmbassadorInfo->id}_queue")
            ? \Cache::get("care_ambassador_{$this->careAmbassadorInfo->id}_queue")
            : [];

        //do not check status for call_queue. If they have been selected by the CA they must have been on call queue initially
        //per CPM-2256 we will be applying statuses on confirmed family members, so that we can pre-fill their data on the page.
        if ( ! empty($queue)) {
            $nextEnrolleeId = collect($queue)->first();

            return Enrollee::withCaPanelRelationships()
                ->find($nextEnrolleeId);
        }

        return null;
    }

    /**
     * @return mixed
     */
    private function getFromCallQueue()
    {
        return Enrollee::withCaPanelRelationships()
            ->lessThanThreeAttempts()
            ->whereCareAmbassadorUserId($this->careAmbassadorInfo->user_id)
            ->where('status', Enrollee::TO_CALL)
            ->first();
    }

    /**
     * @return mixed
     */
    private function getPendingConfirmedFamilyMembers()
    {
        return Enrollee::withCaPanelRelationships()
            ->whereIn('status', Enrollee::TO_CONFIRM_STATUSES)
            ->whereCareAmbassadorUserId($this->careAmbassadorInfo->user_id)
            ->first();
    }

    /**
     * @return mixed
     */
    private function getRequestedCallbackToday()
    {
        return Enrollee::withCaPanelRelationships()
            //added < just in case CA missed them/did not work etc.
            ->where('requested_callback', '<=', Carbon::now()->toDateString())
            ->whereIn('status', [
                Enrollee::TO_CALL,
                Enrollee::UNREACHABLE,
            ])
            ->whereCareAmbassadorUserId($this->careAmbassadorInfo->user_id)
            //make sure that most recently updated comes first: e.g. Enrollee that just has been marked for callback from CA Director
            ->orderByDesc('updated_at')
            ->first();
    }

    /**
     * @return mixed
     */
    private function getUtcAttemptCount()
    {
        $days = isProductionEnv() ? self::DAYS_FOR_NEXT_ATTEMPT : minDaysPastForCareAmbassadorNextAttempt();

        return Enrollee::withCaPanelRelationships()
            ->whereCareAmbassadorUserId($this->careAmbassadorInfo->user_id)
            ->lessThanThreeAttempts()
            ->whereStatus(Enrollee::UNREACHABLE)
            ->where('last_attempt_at', '<', Carbon::now()->subDays($days))
            //important. Patient has 1 attempt and has been called 3 days ago. However then they requested that they be called in 10 days
            //thus they will be picked up by method 'getRequestedCallbackToday' in 10 days.
            ->whereNull('requested_callback')
            ->orderBy('attempt_count')
            ->first();
    }

    /**
     * @return |null
     */
    private function retrieve()
    {
        foreach ($this->priority as $function) {
            /**
             * @var Enrollee
             */
            $enrollee = $this->$function();

            if ( ! $enrollee) {
                continue;
            }

            if (
                $enrollee->speaksSpanish() && ! $this->careAmbassadorInfo->speaks_spanish && ! in_array($function, ['getFromCache',
                    'getPendingConfirmedFamilyMembers', ])
            ) {
                //return to CA Director page to be assigned again
                $enrollee->care_ambassador_user_id = null;
                $enrollee->save();

                continue;
            }

            //re-assign care ambassador, in case patient has been retrieved as a confirmed family member
            $enrollee->care_ambassador_user_id = $this->careAmbassadorInfo->user_id;
            $enrollee->save();

            return $enrollee;
        }

        return null;
    }
}
