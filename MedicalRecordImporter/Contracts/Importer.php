<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator;
use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;

/**
 * This is a Section Importer. It allows for each Health Section to be able to be imported for QA.
 *
 * Interface Importer
 */
interface Importer
{
    public function chooseValidator($item);

    /**
     * This will import a Section (eg. Problems, Demographics, Meds), and attach it to an ImportedMedicalRecord for QA.
     *
     * @param $medicalRecordId
     * @param $medicalRecordType
     *
     * @return mixed
     */
    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    );

    public function validate($item);

    /**
     * @return Validator[]
     */
    public function validators(): array;
}
