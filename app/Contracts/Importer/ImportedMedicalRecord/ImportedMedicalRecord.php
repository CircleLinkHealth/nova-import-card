<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 12:53 AM
 */

namespace App\Contracts\Importer\ImportedMedicalRecord;


use App\CarePlan;
use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Practice;
use App\User;

interface ImportedMedicalRecord
{
    /**
     * Get the Allergies that were imported for QA
     */
    public function allergies();

    /**
     * Get the Demographics that were imported for QA
     *
     * @return DemographicsImport[]
     */
    public function demographics();

    /**
     * Get the Medications that were imported for QA
     *
     * @return \App\Importer\Models\ImportedItems\MedicationImport[]
     */
    public function medications();

    /**
     * Get the Problems that were imported for QA
     *
     * @return \App\Importer\Models\ImportedItems\ProblemImport[]
     */
    public function problems();

    public function medicalRecord() : MedicalRecord;

    public function getPractice() : Practice;

    public function providers() : array;

    public function getBillingProvider() : User;

    public function createCarePlan() : CarePlan;

    public function reimport() : self;

}