<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use App\Call;
use App\Jobs\StoreTimeTracking;
use App\Note;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Symfony\Component\HttpFoundation\ParameterBag;

trait TimeHelpers
{
    /**
     * Add billable or not to a patient and credit nurse.
     *
     * @param User|null  $patient
     * @param mixed      $enrolleeId
     * @param mixed|null $activityName
     * @param mixed      $forceSkip
     */
    private function addTime(
        User $nurse,
        $patient,
        int $minutes,
        bool $billable,
        bool $withSuccessfulCall = false,
        bool $bhiTime = false,
        Carbon $startTime = null,
        $enrolleeId = 0,
        $activityName = null,
        $forceSkip = false
    ) {
        if ($withSuccessfulCall) {
            /** @var Note $fakeNote */
            $fakeNote               = \factory(Note::class)->make();
            $fakeNote->author_id    = $nurse->id;
            $fakeNote->patient_id   = $patient->id;
            $fakeNote->status       = Note::STATUS_COMPLETE;
            $fakeNote->performed_at = $startTime ?? now();
            $fakeNote->save();

            /** @var Call $fakeCall */
            $fakeCall                  = \factory(Call::class)->make();
            $fakeCall->note_id         = $fakeNote->id;
            $fakeCall->status          = Call::REACHED;
            $fakeCall->inbound_cpm_id  = $patient->id;
            $fakeCall->outbound_cpm_id = $nurse->id;
            $fakeCall->called_date     = $startTime ?? now();
            $fakeCall->save();
        }

        if ( ! $activityName) {
            $activityName = $withSuccessfulCall
                ? 'Patient Note Creation'
                : 'test';
        }

        $seconds = $minutes * 60;
        $bag     = new ParameterBag();
        $bag->add([
            'providerId' => $nurse->id,
            'patientId'  => $billable
                ? $patient->id
                : 0,
            'activities' => [
                [
                    'is_behavioral' => $bhiTime,
                    'duration'      => $seconds,
                    'start_time'    => $startTime ?? Carbon::now(),
                    'name'          => $activityName,
                    'title'         => 'test',
                    'url'           => 'test',
                    'url_short'     => 'test',
                    'enrolleeId'    => $enrolleeId,
                    'force_skip'    => $forceSkip,
                ],
            ],
        ]);

        StoreTimeTracking::dispatchNow($bag);
    }
}
