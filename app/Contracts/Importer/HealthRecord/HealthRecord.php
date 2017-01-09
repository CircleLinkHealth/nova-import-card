<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 12:29 AM
 */

namespace App\Contracts\Importer\HealthRecord;

use App\CLH\Contracts\CCD\HealthRecordSectionLog;
use App\Contracts\Importer\ImportedHealthRecord\ImportedHealthRecord;
use App\User;

/**
 * This is any Health Record that can be Imported.
 * Examples include a Ccda, a CsvList and so on.
 *
 * Interface HealthRecord
 * @package App\Contracts\Importer
 */
interface HealthRecord
{
    /**
     * Handles importing a HealthRecord for QA.
     *
     * @return ImportedHealthRecord
     *
     */
    public function import() : ImportedHealthRecord;

    /**
     * Transform the data into HealthRecordSectionLogs, so that they can be fed to the Importer
     *
     * @return HealthRecordSectionLog|HealthRecord
     */
    public function createLogs() : HealthRecord;

    /**
     * Get the Transformer
     *
     * @return HealthRecordLogger
     */
    public function getLogger() : HealthRecordLogger;

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return User
     */
    public function getPatient() : User;

    /**
     * Import Allergies for QA
     *
     * @return HealthRecord
     */
    public function importAllergies() : HealthRecord;

    /**
     * Import Demographics for QA
     *
     * @return HealthRecord
     */
    public function importDemographics() : HealthRecord;

    /**
     * Import Document for QA
     *
     * @return HealthRecord
     */
    public function importDocument() : HealthRecord;

    /**
     * Import Medications for QA
     *
     * @return HealthRecord
     */
    public function importMedications() : HealthRecord;

    /**
     * Import Problems for QA
     *
     * @return HealthRecord
     */
    public function importProblems() : HealthRecord;

    /**
     * Import Providers for QA
     *
     * @return HealthRecord
     */
    public function importProviders() : HealthRecord;
}