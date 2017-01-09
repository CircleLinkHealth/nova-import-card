<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 12:53 AM
 */

namespace App\Contracts\Importer\ImportedHealthRecord;


use App\CarePlan;
use App\CLH\CCD\ImportedItems\AllergyImport;
use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\ImportedItems\MedicationImport;
use App\CLH\CCD\ImportedItems\ProblemImport;
use App\Contracts\Importer\HealthRecord\HealthRecord;
use App\Practice;
use App\User;

interface ImportedHealthRecord
{
    /**
     * Get the Allergies that were imported for QA
     *
     * @return AllergyImport[]
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
     * @return MedicationImport[]
     */
    public function getMedications() : array;

    /**
     * Get the Problems that were imported for QA
     *
     * @return ProblemImport[]
     */
    public function getProblems() : array;

    public function getHealthRecord() : HealthRecord;

    public function getPractice() : Practice;

    public function providers() : array;

    public function billingProvider() : User;

    public function createCarePLan() : CarePlan;

    public function reimport() : self;

}