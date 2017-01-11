<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 2:23 AM
 */

namespace App\Contracts\Importer\MedicalRecord;


interface MedicalRecordLogger
{
    /**
     * Log all Sections.
     */
    public function logAllSections();

    /**
     * Log Allergies Section.
     * @return MedicalRecordLogger
     */
    public function logAllergiesSection() : MedicalRecordLogger;

    /**
     * Log Demographics Section.
     * @return MedicalRecordLogger
     */
    public function logDemographicsSection() : MedicalRecordLogger;

    /**
     * Log Document Section.
     * @return MedicalRecordLogger
     */
    public function logDocumentSection() : MedicalRecordLogger;

    /**
     * Log Medications Section.
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection() : MedicalRecordLogger;

    /**
     * Log Problems Section.
     * @return MedicalRecordLogger
     */
    public function logProblemsSection() : MedicalRecordLogger;

    /**
     * Log Providers Section.
     * @return MedicalRecordLogger
     */
    public function logProvidersSection() : MedicalRecordLogger;
}