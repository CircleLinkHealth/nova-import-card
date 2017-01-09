<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 2:23 AM
 */

namespace App\Contracts\Importer\HealthRecord;


interface HealthRecordLogger
{
    /**
     * Log all Sections.
     * @return bool
     */
    public function logAllSections() : bool;

    /**
     * Log Allergies Section.
     * @return HealthRecordLogger
     */
    public function logAllergiesSection() : HealthRecordLogger;

    /**
     * Log Demographics Section.
     * @return HealthRecordLogger
     */
    public function logDemographicsSection() : HealthRecordLogger;

    /**
     * Log Document Section.
     * @return HealthRecordLogger
     */
    public function logDocumentSection() : HealthRecordLogger;

    /**
     * Log Medications Section.
     * @return HealthRecordLogger
     */
    public function logMedicationsSection() : HealthRecordLogger;

    /**
     * Log Problems Section.
     * @return HealthRecordLogger
     */
    public function logProblemsSection() : HealthRecordLogger;

    /**
     * Log Providers Section.
     * @return HealthRecordLogger
     */
    public function logProvidersSection() : HealthRecordLogger;
}