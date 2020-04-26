<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use App\CareAmbassador;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

/**
 * Class EnrollableCallQueue.
 */
class EnrollableCallQueue
{
    /**
     * @var CareAmbassador
     */
    protected $careAmbassadorInfo;

    /**
     * get all careAmbassador enrollees / do not prioritize speaks spanish
     * get all CA enrollees.
     * TOP PRIO - patients in queue (confirmed family members)
     * 1st prio - Confirmed family members whom statuses have not been confirmed - edge case - add UI
     * 2nd prio - utc patients where attempt count 1 && last attempt > 3 days ago
     * 3nd prio - >> attempt count 2
     * 4th prio - call queue
     * Post conditions - never bring enrolled, consented, soft or hard decline, utc x3, ineligible, legacy
     * if patient is spanish and CA does not speak spanish, re-assign.
     *
     * @var array
     */
    protected $priority = [
        'getFromCache',
        'getPendingConfirmedFamilyMembers',
        'getRequestedCallbackToday',
        'getUtcAttemptCount',
        'getFromCallQueue',
    ];

    public function __construct(CareAmbassador $careAmbassadorInfo)
    {
        $this->careAmbassadorInfo = $careAmbassadorInfo;
    }

    /**
     * @return |null
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
        //today or earliest/past
        return Enrollee::withCaPanelRelationships()
            ->where('requested_callback', Carbon::now()->toDateString())
            ->whereIn('status', [
                Enrollee::TO_CALL,
                Enrollee::UNREACHABLE,
            ])
            ->whereCareAmbassadorUserId($this->careAmbassadorInfo->user_id)
            ->orderBy('attempt_count')
            ->first();
    }

    /**
     * @return mixed
     */
    private function getUtcAttemptCount()
    {
        return Enrollee::withCaPanelRelationships()
            //does it need status here? Yes it does. What if it's rejected on 2 attempt
            ->whereStatus(Enrollee::UNREACHABLE)
            ->when( ! isProductionEnv(), function ($q) {
                $q->where('last_attempt_at', '<', Carbon::now()->subDays(minDaysPastForCareAmbassadorNextAttempt()));
            })
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
                $enrollee->speaksSpanish() && $this->careAmbassadorInfo->speaks_spanish && ! in_array($function, ['getFromCache',
                    'getPendingConfirmedFamilyMembers', ])
            ) {
                //assign to care-ambassador that speaks spanish, or return to CA Director page to be assigned again
                $enrollee->care_ambassador_user_id = optional(CareAmbassador::whereSpeaksSpanish(true)->first())->user_id;
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
