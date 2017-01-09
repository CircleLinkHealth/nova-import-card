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
     * This handles parsing a resource and storing it for QA.
     * Parsing a resource means it
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
    public function transform() : self;

    /**
     * Get the Transformer
     *
     * @return HealthRecordTransformer
     */
    public function getTransformer() : HealthRecordTransformer;

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
    public function importAllergies() : self;

    /**
     * Import Demographics for QA
     *
     * @return HealthRecord
     */
    public function importDemographics() : self;

    /**
     * Import Document for QA
     *
     * @return HealthRecord
     */
    public function importDocument() : self;

    /**
     * Import Medications for QA
     *
     * @return HealthRecord
     */
    public function importMedications() : self;

    /**
     * Import Problems for QA
     *
     * @return HealthRecord
     */
    public function importProblems() : self;

    /**
     * Import Providers for QA
     *
     * @return HealthRecord
     */
    public function importProviders() : self;
}