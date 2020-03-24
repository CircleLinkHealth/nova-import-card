<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordLogger;
use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;

use CircleLinkHealth\Customer\Entities\User;

/**
 * This is any Health Record that can be Imported.
 * Examples include a Ccda, a CsvList and so on.
 *
 * Interface MedicalRecordForEligibilityCheck
 */
interface MedicalRecord
{
    /**
     * status: A careplan was created.
     */
    const CAREPLAN_CREATED = 'careplan_created';

    /**
     * status: A record created for the purpose of determining whether the patient is eligible to be called for
     * enrollment.
     */
    const DETERMINE_ENROLLEMENT_ELIGIBILITY = 'determine_enrollement_eligibility';

    /**
     * status: The patient is eligible ot be called for enrollment.
     */
    const ELIGIBLE = 'eligible';

    /**
     * status: An error occured while processing the CCDA.
     */
    const ERROR = 'error';

    /**
     * status: The CCD is ready to be imported.
     */
    const IMPORT = 'import';

    /**
     * status: The patient is ineligible ot be called for enrollment.
     */
    const INELIGIBLE = 'ineligible';

    /**
     * status: The CCD is invalid. Example: the xml file is empty.
     */
    const INVALID = 'invalid';

    /**
     * status: The patient has consented to enrolling to CCM.
     */
    const PATIENT_CONSENTED = 'patient_consented';

    /**
     * status: The patient has declined enrolling to CCM.
     */
    const PATIENT_DECLINED = 'patient_declined';

    /**
     * status: The imported CCD is undergoing QA process.
     */
    const QA = 'qa';

    /**
     * Transform the data into MedicalRecordSectionLogs, so that they can be fed to the Importer.
     *
     * @return ItemLog|MedicalRecord
     */
    public function createLogs(): MedicalRecord;

    public function getBillingProviderId(): ?int;

    public function getDocumentCustodian(): string;

    public function getId(): ?int;

    public function getLocationId(): ?int;

    /**
     * Get the Transformer.
     */
    public function getLogger(): MedicalRecordLogger;

    /**
     * Get the User to whom this record belongs to, if one exists.
     */
    public function getPatient(): ?User;

    public function getPracticeId(): ?int;

    public function getType(): ?string;

    /**
     * Guess Practice, Location and Billing Provider.
     */
    public function guessPracticeLocationProvider(): MedicalRecord;

    /**
     * Handles importing a MedicalRecordForEligibilityCheck for QA.
     *
     * @return ImportedMedicalRecord
     */
    public function import();

    /**
     * Import Allergies for QA.
     */
    public function importAllergies(): MedicalRecord;

    /**
     * Import Demographics for QA.
     */
    public function importDemographics(): MedicalRecord;

    /**
     * Import Document for QA.
     */
    public function importDocument(): MedicalRecord;

    /**
     * Import Insurance Policies for QA.
     */
    public function importInsurance(): MedicalRecord;

    /**
     * Import Medications for QA.
     */
    public function importMedications(): MedicalRecord;

    /**
     * Import Problems for QA.
     */
    public function importProblems(): MedicalRecord;

    /**
     * Import Providers for QA.
     */
    public function importProviders(): MedicalRecord;
}
