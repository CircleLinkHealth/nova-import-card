<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Importer;

interface EligibilityProcessable
{
    //may be redundant because of the above
    public function getCsvPatientData();

    public function getEligibilityBatch();

    public function getEligibilityJob();

    //returns data prepared to check if enrollee exists, create or update enrollee
    //or get patientData? Basically to prepare the data that will all the if checks go away.
    public function getEnrolleeData();

    //fetches insurances as a collection, adapts data to primary secondary and tertiary if possible. -(JsonMedicalRecordInsurancePlansAdapter())
    public function getInsurances();

    //fetches last encounter as a Carbon Object
    //if last encounter does not exist, is not filled or not a date, returns null
    public function getLastEncounter();

    public function getMedicalRecordId();

    public function getMedicalRecordType();

    public function getPractice();

    //fetches Problems as a collection of Problem::class object (performs handling from Problem Loggers)
    public function getProblems();
}
