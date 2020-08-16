<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NextCallCalculator;

use App\Algorithms\Calls\NurseFinderRepository;
use App\Call;
use App\Contracts\CallHandler;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\User;

class NextCallDateCalculator
{
    public function handle(User $patient, CallHandler $handler)
    {
        return $this->getPredicament(
            $this->getAssignedNurse(
                $this->formatAlgoDataForView(
                    $this->getNextPatientWindow(
                        $this->getNextCallDate(
                            $this->initializePrediction($patient),
                            $handler
                        )
                    )
                )
            ),
            $handler
        );
    }

    private function formatAlgoDataForView(NextCallPrediction $prediction)
    {
        $ccm_time_achieved = false;
        if ($prediction->ccmTimeInSeconds >= 1200) {
            $ccm_time_achieved = true;
        }

        $H                    = floor($prediction->ccmTimeInSeconds / 3600);
        $i                    = ($prediction->ccmTimeInSeconds / 60) % 60;
        $s                    = $prediction->ccmTimeInSeconds % 60;
        $formattedMonthlyTime = sprintf('%02d:%02d:%02d', $H, $i, $s);

        $prediction->ccm_time_achieved      = $ccm_time_achieved;
        $prediction->formatted_monthly_time = $formattedMonthlyTime;
        $prediction->attempt_note           = '';

        return $prediction;
    }

    private function getAssignedNurse(NextCallPrediction $prediction): NextCallPrediction
    {
        if ($nurse = app(NurseFinderRepository::class)->find($prediction->patient->id)) {
            $prediction->nurse              = $nurse->id;
            $prediction->nurse_display_name = $nurse->display_name;
            $prediction->window_match       = "Assigning next call to {$nurse->display_name}.";
        } else {
            $prediction->nurse              = null;
            $prediction->nurse_display_name = null;
            $prediction->window_match       = 'This patient has no assigned nurse in CPM.';
        }

        return $prediction;
    }

    private function getNextCallDate(NextCallPrediction $prediction, CallHandler $handler): NextCallPrediction
    {
        $response = $handler->getNextCallDate(
            $prediction->patient->id,
            $prediction->ccmTimeInSeconds,
            now()->weekOfMonth,
            $prediction->no_of_successful_calls,
            $prediction->patient && optional($prediction->patient->patientInfo)->preferred_calls_per_month ?? Patient::DEFAULT_CALLS_PER_MONTH
        );
        $prediction->attempt_note = $response->attemptNote;
        $prediction->logic        = $response->reasoning;
        $prediction->nextCallDate = $response->nextCallDate;

        return $prediction;
    }

    private function getNextPatientWindow(NextCallPrediction $prediction)
    {
        if ('Call This Weekend' != $prediction->attempt_note) {
            $next_predicted_contact_window['day']          = $prediction->nextCallDate->next(Carbon::SATURDAY)->toDateString();
            $next_predicted_contact_window['window_start'] = '10:00:00';
            $next_predicted_contact_window['window_end']   = '17:00:00';
        } elseif ($prediction->patient->patientInfo) {
            $next_predicted_contact_window = (new PatientContactWindow())
                ->getEarliestWindowForPatientFromDate(
                    $prediction->patient->patientInfo,
                    $prediction->nextCallDate
                );
        } else {
            $next_predicted_contact_window['day']          = $prediction->nextCallDate->toDateString();
            $next_predicted_contact_window['window_start'] = '10:00:00';
            $next_predicted_contact_window['window_end']   = '17:00:00';
        }

        $prediction->date         = $next_predicted_contact_window['day'];
        $prediction->window_start = $next_predicted_contact_window['window_start'];
        $prediction->window_end   = $next_predicted_contact_window['window_end'];

        return $prediction;
    }

    private function getPredicament(NextCallPrediction $prediction, CallHandler $handler): NextCallPrediction
    {
        $prediction->predicament = $handler->createSchedulerInfoString($prediction);

        return $prediction;
    }

    private function initializePrediction(User $patient): NextCallPrediction
    {
        $prediction                         = new NextCallPrediction();
        $prediction->patient                = $patient;
        $prediction->ccmTimeInSeconds       = $prediction->patient->getCcmTime();
        $prediction->no_of_successful_calls = Call::numberOfSuccessfulCallsForPatientForMonth(
            $prediction->patient->id,
            Carbon::now()
        );

        return $prediction;
    }
}
