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
     * status: A record created for the purpose of determining whether the patient is eligible to be called for enrollment.
     */
    const DETERMINE_ENROLLEMENT_ELIGIBILITY = 'determine_enrollement_eligibility';

    /**
     * status: The patient is eligible ot be called for enrollment.
     */
    const ELIGIBLE = 'eligible';

    /**
     * status: The patient is ineligible ot be called for enrollment.
     */
    const INELIGIBLE = 'ineligible';

    /**
     * status: The patient has consented to enrolling to CCM.
     */
    const PATIENT_CONSENTED = 'patient_consented';

    /**
     * status: The patient has declined enrolling to CCM.
     */
    const PATIENT_DECLINED = 'patient_declined';

    /**
     * status: The CCD is ready to be imported.
     */
    const IMPORT = 'import';

    /**
     * status: The imported CCD is undergoing QA process.
     */
    const QA = 'qa';

    /**
     * status: A careplan was created.
     */
    const CAREPLAN_CREATED = 'careplan_created';

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
    public function createLogs(): MedicalRecord;

    /**
     * Get the Transformer
     *
     * @return MedicalRecordLogger
     */
    public function getLogger(): MedicalRecordLogger;

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return User
     */
    public function getPatient(): User;

    /**
     * Import Allergies for QA
     *
     * @return MedicalRecord
     */
    public function importAllergies(): MedicalRecord;

    /**
     * Import Demographics for QA
     *
     * @return MedicalRecord
     */
    public function importDemographics(): MedicalRecord;

    /**
     * Import Document for QA
     *
     * @return MedicalRecord
     */
    public function importDocument(): MedicalRecord;

    /**
     * Import Insurance Policies for QA
     *
     * @return MedicalRecord
     */
    public function importInsurance(): MedicalRecord;

    /**
     * Import Medications for QA
     *
     * @return MedicalRecord
     */
    public function importMedications(): MedicalRecord;

    /**
     * Import Problems for QA
     *
     * @return MedicalRecord
     */
    public function importProblems(): MedicalRecord;

    /**
     * Import Providers for QA
     *
     * @return MedicalRecord
     */
    public function importProviders(): MedicalRecord;

    /**
     * Predict which Practice should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictPractice(): MedicalRecord;

    /**
     * Predict which Location should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictLocation(): MedicalRecord;

    /**
     * Predict which BillingProvider should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictBillingProvider(): MedicalRecord;

    public function getDocumentCustodian(): string;
}
