<?php namespace App\Importer\Loggers\Csv;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\TabularMedicalRecord;

class TabularMedicalRecordSectionsLogger implements MedicalRecordLogger
{
    /**
     * The Medical Record
     *
     * @var TabularMedicalRecord
     */
    private $medicalRecord;

    public function __construct(TabularMedicalRecord $tmr)
    {
        $this->medicalRecord = $tmr;

        $this->foreignKeys = [
            'vendor_id'           => '1',
            'medical_record_type' => TabularMedicalRecord::class,
            'medical_record_id'   => $tmr->id,
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
        $allergies = explode(',', $this->medicalRecord->allergies);

        foreach ($allergies as $allergy) {

            if (strtolower($allergy) == 'no') {
                continue;
            }

            $allergy = AllergyLog::create(
                array_merge([
                    'allergen_name' => trim($allergy),
                ], $this->foreignKeys)
            );
        }

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
                'first_name'    => $this->medicalRecord->first_name,
                'last_name'     => $this->medicalRecord->last_name,
                'dob'           => $this->medicalRecord->dob,
                'provider_name' => $this->medicalRecord->provider_name,
                'phone'         => $this->medicalRecord->phone,
                'mrn_number'    => $this->medicalRecord->mrn,
                'gender'        => $this->medicalRecord->gender,
                'language'      => $this->medicalRecord->language ?? 'EN',
                'primary_phone' => $this->medicalRecord->primary_phone,
                'cell_phone'    => $this->medicalRecord->cell_phone,
                'home_phone'    => $this->medicalRecord->home_phone,
                'work_phone'    => $this->medicalRecord->work_phone,
                'email'         => $this->medicalRecord->email,
                'street'        => $this->medicalRecord->address,
                'street2'       => $this->medicalRecord->address2,
                'city'          => $this->medicalRecord->city,
                'state'         => $this->medicalRecord->state,
                'zip'           => $this->medicalRecord->zip,
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
        $insurances = [];

        if ($this->medicalRecord->primary_insurance) {
            $insurances['primary'] = $this->medicalRecord->primary_insurance;
        }

        if ($this->medicalRecord->secondary_insurance) {
            $insurances['secondary'] = $this->medicalRecord->secondary_insurance;
        }

        if ($this->medicalRecord->tertiary_insurance) {
            $insurances['tertiary'] = $this->medicalRecord->tertiary_insurance;
        }

        foreach ($insurances as $insurance) {
            $insurance = InsuranceLog::create(array_merge([
                'name'     => $insurance,
                'approved' => false,
                'import'   => true,
            ], $this->foreignKeys));
        }

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
        $medications = explode("\n", $this->medicalRecord->medications);

        foreach ($medications as $medication) {
            $explodedMed = explode(',', $medication);

            $medication = MedicationLog::create(
                array_merge([
                    'reference_title' => trim(str_replace([
                        'Taking',
                        'Continue',
                    ], '', $explodedMed[0])),
                    'reference_sig'   => trim(str_replace('Sig:', '', $explodedMed[1])),
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Problems Section.
     * @return MedicalRecordLogger
     */
    public function logProblemsSection() : MedicalRecordLogger
    {
        $problems = explode(',', $this->medicalRecord->problems);

        foreach ($problems as $problem) {
            $problem = trim($problem);

            if (ctype_alpha(str_replace([
                "\n",
                "\t",
                ' ',
            ], '', $problem))) {
                $problem = ProblemLog::create(
                    array_merge([
                        'name' => $problem,
                    ], $this->foreignKeys)
                );
            }
        }

        return $this;
    }

    /**
     * Log Providers Section.
     * @return MedicalRecordLogger
     */
    public function logProvidersSection() : MedicalRecordLogger
    {
        $name = explode(',', $this->medicalRecord->provider_name);

        $provider = ProviderLog::create(array_merge([
            'first_name' => trim($name[1]),
            'last_name'  => trim($name[0]),
        ], $this->foreignKeys));

        return $this;
    }
}