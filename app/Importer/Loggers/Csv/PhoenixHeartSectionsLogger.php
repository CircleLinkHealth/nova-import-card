<?php namespace App\Importer\Loggers\Csv;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance;

class PhoenixHeartSectionsLogger extends TabularMedicalRecordSectionsLogger
{
    /**
     * Log Allergies Section.
     * @return MedicalRecordLogger
     */
    public function logAllergiesSection(): MedicalRecordLogger
    {
        $allergies = PhoenixHeartAllergy::wherePatientId($this->medicalRecord->mrn)->get();

        foreach ($allergies as $allergy) {
            $allergyLog = AllergyLog::create(
                array_merge([
                    'allergen_name' => ucfirst(strtolower($allergy->name)),
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Medications Section.
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection(): MedicalRecordLogger
    {
        $medications = explode("\n", $this->medicalRecord->medications_string);

        $medications = array_filter($medications);

        foreach ($medications as $medication) {
            $explodedMed = explode(',', $medication);

            $sig = '';

            if (isset($explodedMed[1])) {
                $sig = trim(str_replace('Sig:', '', $explodedMed[1]));
            }

            $medication = MedicationLog::create(
                array_merge([
                    'reference_title' => trim(str_replace([
                        'Taking',
                        'Continue',
                    ], '', $explodedMed[0])),
                    'reference_sig'   => $sig,
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Problems Section.
     * @return MedicalRecordLogger
     */
    public function logProblemsSection(): MedicalRecordLogger
    {
        $problems = json_decode($this->medicalRecord->problems_string);

        if (!$problems) {
            $problems = explode(',', $this->medicalRecord->problems_string);
        }

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

            $problem = ProblemLog::create(
                array_merge([
                    'code' => $problem,
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Providers Section.
     * @return MedicalRecordLogger
     */
    public function logProvidersSection(): MedicalRecordLogger
    {
        $name = explode(',', $this->medicalRecord->provider_name);

        $provider = ProviderLog::create(array_merge([
            'first_name' => trim($name[1]),
            'last_name'  => trim($name[0]),
        ], $this->foreignKeys));

        return $this;
    }
}