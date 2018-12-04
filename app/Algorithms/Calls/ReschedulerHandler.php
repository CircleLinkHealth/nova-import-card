<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 9/17/16
 * Time: 2:14 PM
 */

namespace App\Algorithms\Calls;

use App\Call;
use App\Patient;
use App\PatientContactWindow;
use App\Services\Calls\SchedulerService;
use Carbon\Carbon;

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
    private $schedulerService;

    public function __construct(SchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

    protected $callsToReschedule;
    protected $rescheduledCalls = [];

    public function handle()
    {

        //Collect all calls that were missed.
        $this->callsToReschedule = $this->collectCallsToBeRescheduled();

        $this->handleCalls();

        return $this->rescheduledCalls;
    }

    public function collectCallsToBeRescheduled()
    {
        $calls = Call
            ::where(function ($q) {
                $q->whereNull('type')
                  ->orWhere('type', '=', 'call');
            })
            ->whereStatus('scheduled')
            ->with(['inboundUser'])
            ->where('scheduled_date', '<=', Carbon::now()->toDateString())
            ->get();

        $missed = [];

        /*
         * Check to see if the call is dropped if it's the current day
         * Since we store the date and times separately for other
         * considerations, we have to join them and compare
         * to see if a call was missed on the same day
        */

        foreach ($calls as $call) {
            $end_carbon = Carbon::parse($call->scheduled_date);

            $carbon_hour_end    = Carbon::parse($call->window_end)->format('H');
            $carbon_minutes_end = Carbon::parse($call->window_end)->format('i');

            $end_time = $end_carbon->setTime($carbon_hour_end, $carbon_minutes_end)->toDateTimeString();

            $now_carbon = Carbon::now()->toDateTimeString();

            if ($end_time < $now_carbon) {
                $missed[] = $call;
            }
        }

        return $missed;
    }

    public function handleCalls()
    {
        foreach ($this->callsToReschedule as $call) {
            //Handle Previous Call
            try {
                $call->status    = 'dropped';
                $call->scheduler = 'rescheduler algorithm';
                $call->save();

                $patient = $call->inboundUser
                    ->patientInfo;

                if (is_a($patient, Patient::class)) {
                    if ($patient->hasFamilyId()) {
                        //a call might have already been scheduled for this patient, since its family
                        //so just skip this patient
                        $familyCall = $this->schedulerService->getScheduledCallForPatient($patient->user);
                        if ($familyCall) {
                            continue;
                        }
                    }

                    //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
                    $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate(
                        $patient,
                        Carbon::now()
                    );

                    $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
                    $window_end   = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');
                    $day          = Carbon::parse($next_predicted_contact_window['day'])->toDateString();

                    $this->storeNewCallForPatient($patient, $call, $window_start, $window_end, $day);
                    $this->storeNewCallForFamilyMembers($patient, $call, $window_start, $window_end, $day);
                }
            } catch (\Exception $exception) {
                \Log::critical($exception);
                \Log::info("Call Id $call->id");
                continue;
            }
        }
    }

    private function storeNewCallForFamilyMembers(Patient $patient, $oldCall, $window_start, $window_end, $day)
    {
        if (! $patient->hasFamilyId()) {
            return;
        }

        $familyMembers = $patient->getFamilyMembers($patient);
        if (! empty($familyMembers)) {
            foreach ($familyMembers as $familyMember) {
                $familyMemberCall = $this->schedulerService->getScheduledCallForPatient($familyMember->user);
                //if manually scheduled by nurse or admin, do not do anything
                if ($familyMemberCall && $familyMemberCall->is_manual) {
                    continue;
                }
                $this->storeNewCallForPatient($familyMember, $oldCall, $window_start, $window_end, $day);
            }
        }
    }

    private function storeNewCallForPatient(Patient $patient, $oldCall, $window_start, $window_end, $day)
    {
        $this->rescheduledCalls[] = $this->schedulerService->storeScheduledCall(
            $patient->user->id,
            $window_start,
            $window_end,
            $day,
            'rescheduler algorithm',
            $oldCall->outbound_cpm_id
                ? $oldCall->outbound_cpm_id
                : null,
            '',
            false
        );
    }
}
