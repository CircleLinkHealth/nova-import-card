<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\TaskAcceptsEnrollee;

class AttachDefaultPatientContactWindows extends BaseCcdaImportTask
{
    use TaskAcceptsEnrollee;
    
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

        return $this->enrollee->getPreferredCallDays();
    }

    private function getEnrolleePreferredCallTimes()
    {
        if ( ! $this->enrollee) {
            return null;
        }

        return $this->enrollee->getPreferredCallTimes();
    }
}
