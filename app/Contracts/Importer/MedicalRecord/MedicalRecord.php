<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 12:29 AM
 */

namespace App\Contracts\Importer\MedicalRecord;

use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord;
use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\User;

/**
 * This is any Health Record that can be Imported.
 * Examples include a Ccda, a CsvList and so on.
 *
 * Interface MedicalRecord
 * @package App\Contracts\Importer
 */
interface MedicalRecord
{
    /**
     * Handles importing a MedicalRecord for QA.
     *
     * @return ImportedMedicalRecord
     *
     */
    public function import();

    /**
     * Transform the data into MedicalRecordSectionLogs, so that they can be fed to the Importer
     *
     * @return ItemLog|MedicalRecord
     */
    public function createLogs() : MedicalRecord;

    /**
     * Get the Transformer
     *
     * @return MedicalRecordLogger
     */
    public function getLogger() : MedicalRecordLogger;

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return User
     */
    public function getPatient() : User;

    /**
     * Import Allergies for QA
     *
     * @return MedicalRecord
     */
    public function importAllergies() : MedicalRecord;

    /**
     * Import Demographics for QA
     *
     * @return MedicalRecord
     */
    public function importDemographics() : MedicalRecord;

    /**
     * Import Document for QA
     *
     * @return MedicalRecord
     */
    public function importDocument() : MedicalRecord;

    /**
     * Import Insurance Policies for QA
     *
     * @return MedicalRecord
     */
    public function importInsurance() : MedicalRecord;

    /**
     * Import Medications for QA
     *
     * @return MedicalRecord
     */
    public function importMedications() : MedicalRecord;

    /**
     * Import Problems for QA
     *
     * @return MedicalRecord
     */
    public function importProblems() : MedicalRecord;

    /**
     * Import Providers for QA
     *
     * @return MedicalRecord
     */
    public function importProviders() : MedicalRecord;

    /**
     * Predict which Practice should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictPractice() : MedicalRecord;

    /**
     * Predict which Location should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictLocation() : MedicalRecord;

    /**
     * Predict which BillingProvider should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictBillingProvider() : MedicalRecord;
}