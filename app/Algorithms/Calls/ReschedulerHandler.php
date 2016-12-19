<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 9/17/16
 * Time: 2:14 PM
 */

namespace App\Algorithms\Calls;

use App\Call;
use App\PatientContactWindow;
use App\PatientInfo;
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
            ::whereStatus('scheduled')
            ->where('scheduled_date', '<=', Carbon::now()->toDateString())
            ->get();

        $missed = array();

        /*
         * Check to see if the call is dropped if it's the current day
         * Since we store the date and times separately for other
         * considerations, we have to join them and compare
         * to see if a call was missed on the same day
        */

        foreach ($calls as $call) {

            $end_carbon = Carbon::parse($call->scheduled_date);

            $carbon_hour_end = Carbon::parse($call->window_end)->format('H');
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
            $call->status = 'dropped';
            $call->scheduler = 'rescheduler algorithm';
            $call->save();

            $patient = PatientInfo::where('user_id', $call->inbound_cpm_id)->first();

            if(is_object($patient)) {

                //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
                $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($patient,
                    Carbon::now());

                $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
                $window_end = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');
                $day = Carbon::parse($next_predicted_contact_window['day'])->toDateString();

                $this->rescheduledCalls[] = (new SchedulerService())->storeScheduledCall(
                    $patient->user->id,
                    $window_start,
                    $window_end,
                    $day,
                    'rescheduler algorithm',
                    $call->outbound_cpm_id ? $call->outbound_cpm_id : null,
                    ''
                );
            }

        }

    }

}