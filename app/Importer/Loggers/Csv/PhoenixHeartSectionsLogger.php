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
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem;
use App\User;
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
                'name'   => $insurance->name,
                'import' => true,
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


            $endDate = null;
            $end = null;
            $startDate = null;

            try {
//                $startDate = Carbon::parse($medication->start_date)->toDateTimeString() ?? null;
//                $endDate = Carbon::parse($medication->end_date);
//                $end = $endDate->isFuture()
//                    ? null
//                    : $endDate->toDateTimeString();
            } catch (\Exception $e) {
                //do nothing
            }

            $medicationLog = MedicationLog::updateOrCreate(
                array_merge([
                    'reference_title'  => ucfirst(strtolower($medication->description)),
                    'product_name'     => ucfirst(strtolower($medication->description)),
                    'translation_name' => ucfirst(strtolower($medication->description)),

                    'reference_sig' => $medication->instructions,
                    'product_text'  => $medication->instructions,
                    'text'          => $medication->instructions,
                ], $this->foreignKeys),
                [
//                    'start' => $startDate,
//                    'end'   => $end
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

        if (!$name) {
            return $this;
        }

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
            'first_name'  => trim($name->provider_first_name),
            'last_name'   => trim($name->provider_last_name),
            'practice_id' => $this->practice->id ?? null,
        ], $this->foreignKeys));

        return $this;
    }
}