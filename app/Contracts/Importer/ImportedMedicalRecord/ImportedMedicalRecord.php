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
     *
     * @return \App\Importer\Models\ImportedItems\AllergyImport[]
     */
    public function getAllergies() : array;

    /**
     * Get the Demographics that were imported for QA
     *
     * @return DemographicsImport[]
     */
    public function getDemographics() : array;

    /**
     * Get the Medications that were imported for QA
     *
     * @return \App\Importer\Models\ImportedItems\MedicationImport[]
     */
    public function getMedications() : array;

    /**
     * Get the Problems that were imported for QA
     *
     * @return \App\Importer\Models\ImportedItems\ProblemImport[]
     */
    public function getProblems() : array;

    public function getMedicalRecord() : MedicalRecord;

    public function getPractice() : Practice;

    public function providers() : array;

    public function billingProvider() : User;

    public function createCarePLan() : CarePlan;

    public function reimport() : self;

}