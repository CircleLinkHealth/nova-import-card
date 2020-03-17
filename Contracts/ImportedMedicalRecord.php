<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

interface ImportedMedicalRecord
{
    /**
     * Get the Allergies that were imported for QA.
     */
    public function allergies();

    public function updateOrCreateCarePlan(): CarePlan;

    /**
     * Get the Demographics that were imported for QA.
     *
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport[]
     */
    public function demographics();

    public function getBillingProvider(): User;

    public function getPractice(): Practice;

    /**
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\\CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordForEligibilityCheck|null
     */
    public function medicalRecord();

    /**
     * Get the Medications that were imported for QA.
     *
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationImport[]
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
