<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Hooks;

use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportHook;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;

class FetchMedicationsFromAthenaApi extends BaseCcdaImportHook
{
    const IMPORTING_LISTENER_NAME = 'fetch_medications_from_athena_api';

    public function run()
    {
        if ( ! empty($this->ccda->bluebuttonJson()->medications)) {
            return;
        }

        $this->ccda->loadMissing('targetPatient');

        $meds = app(AthenaApiImplementation::class)->getMedications(
            $this->ccda->targetPatient->ehr_patient_id,
            $this->ccda->targetPatient->ehr_practice_id,
            $this->ccda->targetPatient->ehr_department_id
        );
        
        // Incomplete
    }
}
