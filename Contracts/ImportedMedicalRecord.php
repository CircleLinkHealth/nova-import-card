<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Importer\Models\ImportedItems\DemographicsImport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

interface ImportedMedicalRecord
{
    /**
     * Get the Allergies that were imported for QA.
     */
    public function allergies();

    public function createCarePlan(): CarePlan;

    /**
     * Get the Demographics that were imported for QA.
     *
     * @return DemographicsImport[]
     */
    public function demographics();

    public function getBillingProvider(): User;

    public function getPractice(): Practice;

    /**
     * @return MedicalRecord|null
     */
    public function medicalRecord();

    /**
     * Get the Medications that were imported for QA.
     *
     * @return \CircleLinkHealth\CarePlanModels\Entities\MedicationImport[]
     */
    public function medications();

    /**
     * Get the Problems that were imported for QA.
     *
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport[]
     */
    public function problems();

    public function providers(): array;

    public function reimport(): self;
}
