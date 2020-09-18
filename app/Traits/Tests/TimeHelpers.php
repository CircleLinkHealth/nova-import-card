<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\TimeTracking\Jobs\StoreTimeTracking;
use App\Note;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Testing\TestResponse;
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
     * @param mixed      $withPhoneSession
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
        $forceSkip = false,
        $withPhoneSession = false
    ): ?Note {
        if ($withSuccessfulCall) {
            $withPhoneSession = true;
        }

        if ( ! $activityName) {
            $activityName = $withSuccessfulCall
                ? 'Patient Note Creation'
                : 'test';
        }

        $note = null;
        if ('Patient Note Creation' === $activityName || $withPhoneSession || $withSuccessfulCall) {
            $note = $this->createNote($nurse, $patient->id, $withPhoneSession, $withSuccessfulCall, $startTime);
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

        return $note;
    }

    private function createNote(User $author, $patientId, $phoneSession = false, $successfulCall = false, Carbon $startTime = null): ?Note
    {
        $this->be($author);

        $args = [
            'body'       => 'test',
            'patient_id' => $patientId,
        ];

        if ($startTime) {
            $args['performed_at'] = $startTime;
        }

        if ($phoneSession) {
            $args['phone']       = 1;
            $args['call_status'] = $successfulCall ? Call::REACHED : Call::NOT_REACHED;
        }

        /** @var TestResponse $resp */
        $resp = $this->call(
            'POST',
            route('patient.note.store', ['patientId' => $patientId]),
            $args
        );

        self::assertTrue($resp->status() < 400);

        $this->flushSession();

        return Note::where('patient_id', '=', $patientId)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
