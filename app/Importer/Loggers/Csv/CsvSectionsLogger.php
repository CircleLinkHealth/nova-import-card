<?php namespace App\Importer\Loggers\Csv;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Models\MedicalRecords\Csv;

class CsvSectionsLogger implements MedicalRecordLogger
{
    private $csv;

    public function __construct(Csv $csv)
    {
        $this->csv = $csv;

        $this->foreignKeys = [
            'ccda_id'             => '1',
            'vendor_id'           => '1',
            'medical_record_type' => Csv::class,
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