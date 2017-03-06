<?php namespace App\Importer\Loggers\Csv;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Models\MedicalRecords\TabularMedicalRecord;

class TabularMedicalRecordSectionsLogger implements MedicalRecordLogger
{
    /**
     * The Medical Record
     *
     * @var TabularMedicalRecord
     */
    private $csv;

    public function __construct(TabularMedicalRecord $csv)
    {
        $this->csv = $csv;

        $this->foreignKeys = [
            'ccda_id'             => '1',
            'vendor_id'           => '1',
            'medical_record_type' => TabularMedicalRecord::class,
            'medical_record_id'   => $csv->id,
        ];
    }

    /**
     * Log all Sections.
     */
    public function logAllSections()
    {
        $this->logAllergiesSection()
            ->logDemographicsSection()
            ->logDocumentSection()
            ->logInsuranceSection()
            ->logMedicationsSection()
            ->logProblemsSection()
            ->logProvidersSection();
    }

    /**
     * Log Allergies Section.
     * @return MedicalRecordLogger
     */
    public function logAllergiesSection() : MedicalRecordLogger
    {
        return $this;
    }

    /**
     * Log Demographics Section.
     * @return MedicalRecordLogger
     */
    public function logDemographicsSection() : MedicalRecordLogger
    {
        $saved = DemographicsLog::create(
            array_merge([

            ], $this->foreignKeys)
        );

        return $this;
    }

    /**
     * Log Insurance Section.
     * @return MedicalRecordLogger
     */
    public function logInsuranceSection() : MedicalRecordLogger
    {
        return $this;
    }

    /**
     * Log Document Section.
     * @return MedicalRecordLogger
     */
    public function logDocumentSection() : MedicalRecordLogger
    {
        return $this;
    }

    /**
     * Log Medications Section.
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection() : MedicalRecordLogger
    {
        return $this;
    }

    /**
     * Log Problems Section.
     * @return MedicalRecordLogger
     */
    public function logProblemsSection() : MedicalRecordLogger
    {
        return $this;
    }

    /**
     * Log Providers Section.
     * @return MedicalRecordLogger
     */
    public function logProvidersSection() : MedicalRecordLogger
    {
        return $this;
    }
}