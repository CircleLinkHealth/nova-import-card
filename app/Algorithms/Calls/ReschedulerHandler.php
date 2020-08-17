<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Call;
use App\Services\Calls\SchedulerService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\User;

//READ ME:
/*
 *
 * Signature: 'rescheduler algorithm'
 *
 * This algorithm takes all past scheduled calls that were missed,
 * and runs them through a cycle to drop them and create a new
 * call based on the the patient's preferences. The previous
 * call is marked as dropped, and a new scheduled call is
 * created.
 *
 * returns a collection of rescheduled calls.
 *
 */

class ReschedulerHandler
{
    /**
     * We are allowing the nurse some extra hours of time to reschedule calls that were not successful for the day.
     */
    const CUSHION_FOR_NURSE_TO_RESCHEDULE_IN_HOURS = 2;
    protected $callsToReschedule;
    protected $rescheduledCalls = [];
    private $schedulerService;

    public function __construct(SchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

    public function fetchAndReschedulePastMissedCalls()
    {
        Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', 'call');
        })
            ->where('status', Call::SCHEDULED)
            ->with([
                'inboundUser.patientNurseAsPatient.nurse',
                'inboundUser.patientInfo',
                'inboundUser.inboundScheduledCalls' => function ($q) {
                    return $q->where('window_end', '>=', now()->format('H:i'))
                        ->where('scheduled_date', '>=', now()->toDateString());
                },
            ])
            ->where('scheduled_date', '<=', now()->toDateString())
            ->where('window_end', '<=', now()->format('H:i'))
            ->chunkById(100, function ($calls) {
                \DB::transaction(function () use ($calls) {
                    foreach ($calls as $call) {
                        $this->reschedule($call);
                    }
                });
            });
    }

    public function handle()
    {
        $this->fetchAndReschedulePastMissedCalls();
    }

    public function reschedule(Call $call)
    {
        try {
            if (is_null($patientUser = $call->inboundUser)) {
                return;
            }

            if ($this->canBeCarriedOut($call)) {
                return;
            }

            $this->expireCall($call);

            if ($this->hasValidFutureScheduledCall($patientUser)) {
                return;
            }

            if ( ! $patientUser->patientInfo instanceof Patient) {
                return;
            }

            $next_predicted_contact_window = (new PatientContactWindow())
                ->getEarliestWindowForPatientFromDate(
                    $patientUser->patientInfo,
                    Carbon::now()
                );

            $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
            $window_end   = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');
            $day          = Carbon::parse($next_predicted_contact_window['day'])->toDateString();

            $this->storeCallForPatient($patientUser->patientInfo, $nurse = app(NurseFinderEloquentRepository::class)->find($patientUser->id), $window_start, $window_end, $day);
            $this->storeNewCallForFamilyMembers($patientUser->patientInfo, $nurse, $window_start, $window_end, $day);
        } catch (\Exception $exception) {
            \Log::critical($exception);
            \Log::info("Call Id {$call->id}");

            return;
        }
    }

    public static function shouldReschedule(Call $call): bool
    {
        $end_carbon = Carbon::parse($call->scheduled_date);

        $carbon_hour_end    = Carbon::parse($call->window_end)->format('H');
        $carbon_minutes_end = Carbon::parse($call->window_end)->format('i');

        $end_time = $end_carbon->setTime($carbon_hour_end, $carbon_minutes_end);

        if ($end_time->lt(now())) {
            return true;
        }

        return false;
    }

    /**
     * Is it still possible to call this patient on their preferred contact day/time, or are we past the latest possible day/time?
     *
     * @return bool
     */
    private function canBeCarriedOut(Call $call)
    {
        return now()
            ->setTimezone($call->inboundUser->timezone ?? config('app.timezone'))
            ->setTimeFromTimeString($call->window_end)
            ->addHours(self::CUSHION_FOR_NURSE_TO_RESCHEDULE_IN_HOURS)
            ->isFuture();
    }

    private function expireCall(Call $call)
    {
        $call->status    = 'dropped';
        $call->scheduler = 'rescheduler algorithm';
        $call->save();
    }

    private function hasValidFutureScheduledCall(User $patientUser)
    {
        return $patientUser
            ->inboundScheduledCalls
            ->reject(function ($call) {
                return ! $this->canBeCarriedOut($call);
            })->count() > 0;
    }

    private function storeCallForPatient(Patient $patient, ?User $nurse, $window_start, $window_end, $day)
    {
        $this->rescheduledCalls[] = $this->schedulerService->storeScheduledCall(
            $patient->user->id,
            $window_start,
            $window_end,
            $day,
            'rescheduler algorithm',
            optional($nurse)->id,
            '',
            false
        );
    }

    private function storeNewCallForFamilyMembers(Patient $patient, ?User $nurse, $window_start, $window_end, $day)
    {
        if ( ! $patient->hasFamilyId()) {
            return;
        }

        foreach ($patient->getFamilyMembers($patient) as $familyMember) {
            $this->storeCallForPatient($familyMember, $nurse, $window_start, $window_end, $day);
        }
    }
}
