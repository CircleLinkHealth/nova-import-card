<?php namespace App\Importer\Loggers\Csv;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem;
use Carbon\Carbon;

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
     * Log Insurance Section.
     * @return MedicalRecordLogger
     */
    public function logInsuranceSection(): MedicalRecordLogger
    {
        $insurances = PhoenixHeartInsurance::wherePatientId($this->medicalRecord->mrn)
            ->get()
            ->sortBy('order');

        foreach ($insurances as $insurance) {
            $insurance = InsuranceLog::create(array_merge([
                'name'     => $insurance->name,
                'approved' => false,
                'import'   => true,
            ], $this->foreignKeys));
        }

        return $this;
    }

    /**
     * Log Medications Section.
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection(): MedicalRecordLogger
    {
        $medications = PhoenixHeartMedication::wherePatientId($this->medicalRecord->mrn)
            ->get();

        foreach ($medications as $medication) {
            $endDate = Carbon::parse($medication->end_date);

            $medicationLog = MedicationLog::updateOrCreate(
                array_merge([
                    'reference_title' => ucfirst(strtolower($medication->description)),
                    'reference_sig'   => $medication->instructions,
                ], $this->foreignKeys),
                [
                    'start' => Carbon::parse($medication->start_date)->toDateTimeString(),
                    'end'   => $endDate->isFuture()
                        ? null
                        : $endDate->toDateTimeString(),
                ]
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
        $problems = PhoenixHeartProblem::wherePatientId($this->medicalRecord->mrn)
            ->get();

        foreach ($problems as $problem) {
            if (str_contains($problem->code, ['-'])) {
                $pos = strpos($problem->code, '-') + 1;
                $problemCode = mb_substr($problem->code, $pos);
            } elseif (str_contains($problem->code, ['ICD'])) {
                $pos = strpos($problem, 'ICD') + 3;
                $problemCode = mb_substr($problem->code, $pos);
            } else {
                $problemCode = $problem->code;
            }

            $endDate = Carbon::parse($problem->end_date);

            $problemLog = ProblemLog::updateOrCreate(
                array_merge([
                    'name' => $problem->description,
                    'code' => $problemCode,
                ], $this->foreignKeys),
                [
                    'start' => Carbon::parse($problem->start_date)->toDateTimeString(),
                    'end'   => $endDate->isFuture()
                        ? null
                        : $endDate->toDateTimeString(),
                ]
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