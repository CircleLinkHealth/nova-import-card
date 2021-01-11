<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts;

interface MedicalRecordLogger
{
    /**
     * Log Allergies Section.
     */
    public function logAllergiesSection(): MedicalRecordLogger;

    /**
     * Log all Sections.
     */
    public function logAllSections();

    /**
     * Log Demographics Section.
     */
    public function logDemographicsSection(): MedicalRecordLogger;

    /**
     * Log Document Section.
     */
    public function logDocumentSection(): MedicalRecordLogger;

    /**
     * Log Insurance Section.
     */
    public function logInsuranceSection(): MedicalRecordLogger;

    /**
     * Log Medications Section.
     */
    public function logMedicationsSection(): MedicalRecordLogger;

    /**
     * Log Problems Section.
     */
    public function logProblemsSection(): MedicalRecordLogger;

    /**
     * Log Providers Section.
     */
    public function logProvidersSection(): MedicalRecordLogger;
}
