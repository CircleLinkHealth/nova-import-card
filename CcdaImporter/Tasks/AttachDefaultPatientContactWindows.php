<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class AttachDefaultPatientContactWindows extends BaseCcdaImportTask
{
    /** @var Enrollee */
    private $enrollee;

    public static function for(User $patient, Ccda $ccda, Enrollee $enrollee = null)
    {
        $static = new static($patient, $ccda);
        if ($enrollee instanceof Enrollee) {
            $static->setEnrollee($enrollee);
        }

        return $static->import();
    }

    /**
     * @param mixed $enrollee
     */
    public function setEnrollee(Enrollee $enrollee): void
    {
        $this->enrollee = $enrollee;
    }

    protected function import()
    {
        $this->patient->load('patientInfo');

        if ( ! $this->patient->timezone) {
            $this->patient->timezone = optional($this->ccda->location)->timezone ?? 'America/New_York';
        }

        $preferredCallDays  = $this->getEnrolleePreferredCallDays();
        $preferredCallTimes = $this->getEnrolleePreferredCallTimes();

        if ( ! $preferredCallDays && ! $preferredCallTimes) {
            PatientContactWindow::sync(
                $this->patient->patientInfo,
                [
                    1,
                    2,
                    3,
                    4,
                    5,
                ]
            );

            return;
        }

        PatientContactWindow::sync(
            $this->patient->patientInfo,
            $preferredCallDays,
            $preferredCallTimes['start'],
            $preferredCallTimes['end']
        );
    }

    private function getEnrolleePreferredCallDays()
    {
        if ( ! $this->enrollee) {
            return null;
        }

        if (empty($this->enrollee->preferred_days)) {
            return null;
        }

        return parseCallDays($this->enrollee->preferred_days);
    }

    private function getEnrolleePreferredCallTimes()
    {
        if ( ! $this->enrollee) {
            return null;
        }

        if (empty($this->enrollee->preferred_window)) {
            return null;
        }

        return parseCallTimes($this->enrollee->preferred_window);
    }
}
