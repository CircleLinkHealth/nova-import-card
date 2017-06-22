<?php namespace App\Importer\Loggers\Csv;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem;
use App\Models\PatientData\Rappa\RappaData;
use App\Models\PatientData\Rappa\RappaInsAllergy;
use App\User;
use Carbon\Carbon;

class RappaSectionsLogger extends TabularMedicalRecordSectionsLogger
{
    /**
     * Log Allergies Section.
     * @return MedicalRecordLogger
     */
    public function logAllergiesSection(): MedicalRecordLogger
    {
        $allergies = RappaInsAllergy::wherePatientId($this->medicalRecord->mrn)
            ->get();

        foreach ($allergies as $allergy) {
            $allergyLog = AllergyLog::create(
                array_merge([
                    'allergen_name' => ucfirst(strtolower($allergy->allergy)),
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
        $insurances = RappaInsAllergy::wherePatientId($this->medicalRecord->mrn)
            ->first();

        $primaryInsurance = InsuranceLog::create(array_merge([
            'name'   => $insurances->primary_insurance,
            'import' => true,
        ], $this->foreignKeys));

        if ($insurances->secondary_insurance == 'No Secondary Plan') {
            return $this;
        }

        $secondaryInsurance = InsuranceLog::create(array_merge([
            'name'   => $insurances->secondary_insurance,
            'import' => true,
        ], $this->foreignKeys));

        return $this;
    }

    /**
     * Log Medications Section.
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection(): MedicalRecordLogger
    {
        $medications = RappaData::wherePatientId($this->medicalRecord->mrn)
            ->get()
            ->pluck('medication')
            ->unique()
            ->values();

        foreach ($medications as $medication) {
            $medicationLog = MedicationLog::updateOrCreate(
                array_merge([
                    'reference_title'  => ucfirst(strtolower($medication->medication)),
                    'product_name'     => ucfirst(strtolower($medication->medication)),
                    'translation_name' => ucfirst(strtolower($medication->medication)),
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

            $endDate = null;
            $end = null;
            $startDate = null;

            try {
                $startDate = Carbon::parse($problem->start_date)->toDateTimeString() ?? null;
                $endDate = Carbon::parse($problem->end_date);
                $end = $endDate->isFuture()
                    ? null
                    : $endDate->toDateTimeString();
            } catch (\Exception $e) {
                //do nothing
            }

            $problemLog = ProblemLog::updateOrCreate(
                array_merge([
                    'name' => $problem->description,
                    'code' => $problemCode,
                ], $this->foreignKeys),
                [
                    'start' => $startDate,
                    'end'   => $end,
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
        $name = PhoenixHeartName::wherePatientId($this->medicalRecord->mrn)
            ->first();

        $user = User::ofType('provider')
            ->whereFirstName($name->provider_first_name)
            ->whereLastName(explode(' ', $name->provider_last_name)[0])
            ->first();

        if ($user) {
            $provider = ProviderLog::create(array_merge([
                'first_name'          => $user->first_name,
                'last_name'           => $user->last_name,
                'user_id'             => $user->id,
                'billing_provider_id' => $user->id,
                'practice_id'         => $this->practice->id ?? null,
            ], $this->foreignKeys));

            return $this;

        }

        $provider = ProviderLog::create(array_merge([
            'first_name'  => trim($name[1]),
            'last_name'   => trim($name[0]),
            'practice_id' => $this->practice->id ?? null,
        ], $this->foreignKeys));

        return $this;
    }
}