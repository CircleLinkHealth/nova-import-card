<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Contracts;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator;
use CircleLinkHealth\SharedModels\Entities\Ccda;

/**
 * This is a Section CcdaSectionImporter. It allows for each Health Section to be able to be imported for QA.
 *
 * Interface CcdaSectionImporter
 */
interface CcdaSectionImporter
{
    public function chooseValidator($item);
    
    /**
     * This will import a Section (eg. Problems, Demographics, Meds), and attach it to an ImportedMedicalRecord for QA.
     *
     * @param User $patient
     * @param Ccda $ccda
     *
     * @return mixed
     */
    public static function for(
       User $patient,
       Ccda $ccda
    );

    public function validate($item);

    /**
     * @return Validator[]
     */
    public function validators(): array;
}
