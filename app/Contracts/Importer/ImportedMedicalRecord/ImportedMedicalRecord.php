<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Importer\ImportedMedicalRecord;

use App\CarePlan;
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
     * @return \App\Importer\Models\ImportedItems\MedicationImport[]
     */
    public function medications();

    /**
     * Get the Problems that were imported for QA.
     *
     * @return \App\Importer\Models\ImportedItems\ProblemImport[]
     */
    public function problems();

    public function providers(): array;

    public function reimport(): self;
}
