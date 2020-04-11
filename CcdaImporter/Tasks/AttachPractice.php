<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\PracticeRoleUser;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;

class AttachPractice extends BaseCcdaImportTask
{
    protected function import()
    {
        if ($practiceId = $this->ccda->practice_id ) {
            $this->patient->program_id = $practiceId;
            $this->patient->save();
            $this->patient->load('primaryPractice');
        }
    }
}