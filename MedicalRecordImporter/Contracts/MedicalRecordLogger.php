<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts;

interface MedicalRecordLogger
{
    /**
     * Log Allergies Section.
     *
     * @return MedicalRecordLogger
     */
    public function logAllergiesSection(): MedicalRecordLogger;

    /**
     * Log all Sections.
     */
    public function logAllSections();

    /**
     * Log Demographics Section.
     *
     * @return MedicalRecordLogger
     */
    public function logDemographicsSection(): MedicalRecordLogger;

    /**
     * Log Document Section.
     *
     * @return MedicalRecordLogger
     */
    public function logDocumentSection(): MedicalRecordLogger;

    /**
     * Log Insurance Section.
     *
     * @return MedicalRecordLogger
     */
    public function logInsuranceSection(): MedicalRecordLogger;

    /**
     * Log Medications Section.
     *
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection(): MedicalRecordLogger;

    /**
     * Log Problems Section.
     *
     * @return MedicalRecordLogger
     */
    public function logProblemsSection(): MedicalRecordLogger;

    /**
     * Log Providers Section.
     *
     * @return MedicalRecordLogger
     */
    public function logProvidersSection(): MedicalRecordLogger;
}
