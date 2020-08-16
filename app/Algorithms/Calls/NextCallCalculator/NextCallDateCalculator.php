<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NextCallCalculator;

use App\Algorithms\Calls\NurseFinderRepository;
use App\Call;
use App\Contracts\CallHandler;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\User;

class NextCallDateCalculator
{
    private ?int $ccmTime = null;
    private User $patient;

    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }

    public function ccmTime()
    {
        if (is_null($this->ccmTime)) {
            $this->ccmTime = $this->patient->getCcmTime();
        }

        return $this->ccmTime;
    }

    public function formatAlgoDataForView(Prediction $prediction)
    {
        $ccm_time_achieved = false;
        if ($this->ccmTime() >= 1200) {
            $ccm_time_achieved = true;
        }

        $H                    = floor($this->ccmTime() / 3600);
        $i                    = ($this->ccmTime() / 60) % 60;
        $s                    = $this->ccmTime() % 60;
        $formattedMonthlyTime = sprintf('%02d:%02d:%02d', $H, $i, $s);

        $prediction->ccm_time_achieved      = $ccm_time_achieved;
        $prediction->formatted_monthly_time = $formattedMonthlyTime;
        $prediction->attempt_note           = '';

        return $prediction;
    }

    public function getNextPatientWindow(Prediction $prediction)
    {
        if ('Call This Weekend' == $prediction->attempt_note) {
            $next_predicted_contact_window['day']          = $prediction->nextCallDate->next(Carbon::SATURDAY)->toDateString();
            $next_predicted_contact_window['window_start'] = '10:00:00';
            $next_predicted_contact_window['window_end']   = '17:00:00';
        } else {
            $next_predicted_contact_window = (new PatientContactWindow())
                ->getEarliestWindowForPatientFromDate(
                    $prediction->patient->patientInfo,
                    $prediction->nextCallDate
                );
        }

        $prediction->date         = $next_predicted_contact_window['day'];
        $prediction->window_start = $next_predicted_contact_window['window_start'];
        $prediction->window_end   = $next_predicted_contact_window['window_end'];

        return $prediction;
    }

    public function handle(CallHandler $handler)
    {
        return $this->getPredicament(
            $this->getAssignedNurse(
                $this->formatAlgoDataForView(
                    $this->getNextPatientWindow(
                        $this->getNextCallDate(
                            $this->initializePrediction(),
                            $handler
                        )
                    )
                )
            ),
            $handler
        );
    }

    private function getAssignedNurse(Prediction $prediction): Prediction
    {
        if ($nurse = app(NurseFinderRepository::class)->find($this->patient->id)) {
            $prediction->nurse              = $nurse->id;
            $prediction->nurse_display_name = $nurse->display_name;
            $prediction->window_match       = "Assigning next call to {$nurse->display_name}.";
        }

        return $prediction;
    }

    private function getNextCallDate(Prediction $prediction, CallHandler $handler): Prediction
    {
        $response                 = $handler->getNextCallDate($this->patient->id, $this->ccmTime(), now()->weekOfMonth, $prediction->no_of_successful_calls, $this->patient->patientInfo->preferred_calls_per_month);
        $prediction->attempt_note = $response->attemptNote;
        $prediction->logic        = $response->reasoning;
        $prediction->nextCallDate = $response->nextCallDate;

        return $prediction;
    }

    private function getPredicament(Prediction $prediction, CallHandler $handler): Prediction
    {
        $prediction->predicament = $handler->createSchedulerInfoString($prediction);
    }

    private function initializePrediction(): Prediction
    {
        $prediction                         = new Prediction();
        $prediction->patient                = $this->patient;
        $prediction->no_of_successful_calls = Call::numberOfSuccessfulCallsForPatientForMonth(
            $this->patient,
            Carbon::now()->toDateTimeString()
        );

        return $prediction;
    }
}
