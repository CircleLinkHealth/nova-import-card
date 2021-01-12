<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;

class AttachPractice extends BaseCcdaImportTask
{
    protected function import()
    {
        if ($practiceId = $this->ccda->practice_id) {
            $this->patient->program_id = $practiceId;
            $this->patient->save();
            $this->patient->load('primaryPractice');
        }
    }
}
