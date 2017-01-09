<?php

namespace App\Contracts\Importer\HealthRecord;

use App\Contracts\Importer\HealthRecord\Section\Validator;
use App\Contracts\Importer\ImportedHealthRecord\ImportedSection;

/**
 * This is any Section from a Health Record.
 * Examples include Problems, Medications, Demographics, Vitals, Appointments, Insurance and so
 * on.
 *
 * Interface HealthRecordSection
 * @package App\Contracts\CCDA
 */
interface AdaptedSection
{
    /**
     * This handles parsing a section and storing it for QA.
     *
     * @return ImportedSection|mixed
     */
    public function import() : ImportedSection;

    public function validate() : bool;

    public function log() : bool;

    /*
     * the idea here is that there will be a collection of all the validators/fields, and a function returning the appropriate one to use
     *
     * for example, for problem names: [name, translation_name, reference_title] and so on and so forth
     */
    public function getField() : string; //which key to createCarePLan?

    public function getValidator() : Validator; //which validator to use? if has status use status and so on
}