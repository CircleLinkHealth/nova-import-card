<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NextCallSuggestor;

use App\Algorithms\Calls\NextCallSuggestor\Handlers\SuccessfulCall;
use App\Algorithms\Calls\NextCallSuggestor\Suggestion as NextCallSuggestion;
use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Call;
use App\Constants;
use App\Contracts\CallHandler;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\User;

class Suggestor
{
    const DEFAULT_WINDOW_END   = '17:00:00';
    const DEFAULT_WINDOW_START = '10:00:00';

    public function handle(User $patient, CallHandler $handler): NextCallSuggestion
    {
        return $this->getPredicament(
            $this->getAssignedNurse(
                $this->formatAlgoDataForView(
                    $this->getNextPatientWindow(
                        $this->getNextCallDate(
                            $this->initializePrediction($patient, $handler),
                            $handler
                        )
                    )
                )
            ),
            $handler
        );
    }

    private function formatAlgoDataForView(NextCallSuggestion $suggestion)
    {
        $ccm_time_achieved = false;
        if ($suggestion->ccm_time_in_seconds >= 1200) {
            $ccm_time_achieved = true;
        }

        $H                    = floor($suggestion->ccm_time_in_seconds / 3600);
        $i                    = ($suggestion->ccm_time_in_seconds / 60) % 60;
        $s                    = $suggestion->ccm_time_in_seconds % 60;
        $formattedMonthlyTime = sprintf('%02d:%02d:%02d', $H, $i, $s);

        $suggestion->ccm_time_achieved      = $ccm_time_achieved;
        $suggestion->formatted_monthly_time = $formattedMonthlyTime;
        $suggestion->attempt_note           = '';

        return $suggestion;
    }

    private function getAssignedNurse(NextCallSuggestion $suggestion): NextCallSuggestion
    {
        if ($nurse = app(NurseFinderEloquentRepository::class)->find($suggestion->patient->id)) {
            $suggestion->nurse              = $nurse->id;
            $suggestion->nurse_display_name = $nurse->display_name;
            $suggestion->window_match       = "Assigning next call to {$nurse->display_name}.";
        } else {
            $suggestion->nurse              = null;
            $suggestion->nurse_display_name = null;
            $suggestion->window_match       = 'This patient has no assigned nurse in CPM.';
        }

        return $suggestion;
    }

    private function getNextCallDate(NextCallSuggestion $suggestion, CallHandler $handler): NextCallSuggestion
    {
        $response = $handler->getNextCallDate(
            $suggestion->patient->id,
            $suggestion->ccm_time_in_seconds,
            now()->weekOfMonth,
            $suggestion->no_of_successful_calls,
            $suggestion->patient && optional($suggestion->patient->patientInfo)->preferred_calls_per_month ? optional($suggestion->patient->patientInfo)->preferred_calls_per_month : Patient::DEFAULT_CALLS_PER_MONTH
        );
        $suggestion->attempt_note = $response->attemptNote;
        $suggestion->logic        = $response->reasoning;
        $suggestion->nextCallDate = $response->nextCallDate;

        return $suggestion;
    }

    private function getNextPatientWindow(NextCallSuggestion $suggestion)
    {
        if ('Call This Weekend' == $suggestion->attempt_note) {
            $next_predicted_contact_window['day']          = $suggestion->nextCallDate->next(Carbon::SATURDAY)->toDateString();
            $next_predicted_contact_window['window_start'] = self::DEFAULT_WINDOW_START;
            $next_predicted_contact_window['window_end']   = self::DEFAULT_WINDOW_END;
        } elseif ($suggestion->patient->patientInfo) {
            $next_predicted_contact_window = (new PatientContactWindow())
                ->getEarliestWindowForPatientFromDate(
                    $suggestion->patient->patientInfo,
                    $suggestion->nextCallDate
                );
        } else {
            $next_predicted_contact_window['day']          = $suggestion->nextCallDate->toDateString();
            $next_predicted_contact_window['window_start'] = self::DEFAULT_WINDOW_START;
            $next_predicted_contact_window['window_end']   = self::DEFAULT_WINDOW_END;
        }

        $suggestion->date         = $next_predicted_contact_window['day'];
        $suggestion->window_start = $next_predicted_contact_window['window_start'];
        $suggestion->window_end   = $next_predicted_contact_window['window_end'];

        return $suggestion;
    }

    private function getPredicament(NextCallSuggestion $suggestion, CallHandler $handler): NextCallSuggestion
    {
        $suggestion->predicament = $handler->createSchedulerInfoString($suggestion);

        return $suggestion;
    }

    private function initializePrediction(User $patient, CallHandler $handler): NextCallSuggestion
    {
        $suggestion                         = new NextCallSuggestion();
        $suggestion->patient                = $patient;
        $suggestion->successful             = $handler instanceof SuccessfulCall;
        $suggestion->ccm_time_in_seconds    = $suggestion->patient->getCcmTime();
        $suggestion->no_of_successful_calls = Call::numberOfSuccessfulCallsForPatientForMonth(
            $suggestion->patient->id,
            Carbon::now()
        );
        $suggestion->ccm_above = $suggestion->ccm_time_in_seconds >= Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;

        return $suggestion;
    }
}
