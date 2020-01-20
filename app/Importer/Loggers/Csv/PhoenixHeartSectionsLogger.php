<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Csv;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordLogger;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProviderLog;
use CircleLinkHealth\SharedModels\Entities\TabularMedicalRecord;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication;
use CircleLinkHealth\Eligibility\Entities\PhoenixHeartName;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;
use CircleLinkHealth\SharedModels\Entities\AllergyLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationLog;

class PhoenixHeartSectionsLogger extends TabularMedicalRecordSectionsLogger
{
    private $lastPhxImportDate;

    public function __construct(TabularMedicalRecord $tmr, Practice $practice = null)
    {
        parent::__construct($tmr, $practice);

        $this->lastPhxImportDate = PhoenixHeartProblem::orderBy('created_at', 'desc')->firstOrFail()->created_at->toDateTimeString();
    }

    /**
     * Log Allergies Section.
     */
    public function logAllergiesSection(): MedicalRecordLogger
    {
        $allergies = PhoenixHeartAllergy::wherePatientId($this->medicalRecord->mrn)
            ->where('created_at', $this->lastPhxImportDate)
            ->get();

        foreach ($allergies as $allergy) {
            $allergyLog = AllergyLog::create(
                array_merge([
                    'allergen_name' => ucfirst(strtolower($allergy->name)),
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    public function logDemographicsSection(): MedicalRecordLogger
    {
        if ( ! $this->medicalRecord->mrn) {
            $this->medicalRecord->mrn = $this->lookupMrn(
                $this->medicalRecord->first_name,
                $this->medicalRecord->last_name,
                $this->medicalRecord->dob
            );
        }

        if ( ! $this->medicalRecord->gender && $this->medicalRecord->mrn) {
            $phx = PhoenixHeartName::where('patient_id', $this->medicalRecord->mrn)
                ->where('created_at', $this->lastPhxImportDate)
                ->first();

            $this->medicalRecord->gender = $phx
                ? $phx->gender
                : null;
        }

        $this->medicalRecord->save();

        return parent::logDemographicsSection();
    }

    /**
     * Log Insurance Section.
     */
    public function logInsuranceSection(): MedicalRecordLogger
    {
        $insurances = PhoenixHeartInsurance::wherePatientId($this->medicalRecord->mrn)
            ->where('created_at', $this->lastPhxImportDate)
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
     */
    public function logMedicationsSection(): MedicalRecordLogger
    {
        $medications = PhoenixHeartMedication::wherePatientId($this->medicalRecord->mrn)
            ->where('created_at', $this->lastPhxImportDate)
            ->get();

        foreach ($medications as $medication) {
            $endDate   = null;
            $end       = null;
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
     */
    public function logProblemsSection(): MedicalRecordLogger
    {
        $problems = PhoenixHeartProblem::wherePatientId($this->medicalRecord->mrn)
            ->where('created_at', $this->lastPhxImportDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $latestDate = optional($problems->first())
            ->created_at;

        foreach ($problems as $problem) {
            if ($latestDate->toDateString() != $problem->created_at->toDateString()) {
                continue;
            }

            if (str_contains($problem->code, ['-'])) {
                $pos         = strpos($problem->code, '-') + 1;
                $problemCode = mb_substr($problem->code, $pos);
            } elseif (str_contains($problem->code, ['ICD'])) {
                $pos         = strpos($problem, 'ICD') + 3;
                $problemCode = mb_substr($problem->code, $pos);
            } else {
                $problemCode = $problem->code;
            }

            $endDate   = null;
            $end       = null;
            $startDate = null;

//            try {
//                $startDate = Carbon::parse($problem->start_date)->toDateTimeString() ?? null;
//                $endDate   = Carbon::parse($problem->end_date);
//                $end       = $endDate->isFuture()
//                    ? null
//                    : $endDate->toDateTimeString();
//            } catch (\Exception $e) {
//                //do nothing
//            }

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
     */
    public function logProvidersSection(): MedicalRecordLogger
    {
        $name = PhoenixHeartName::wherePatientId($this->medicalRecord->mrn)
            ->where('created_at', $this->lastPhxImportDate)
            ->first();

        if ( ! $name) {
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

    public function lookupMrn($firstName, $lastName, $dob)
    {
        $dob = Carbon::parse($dob)->toDateString();

        $row = PhoenixHeartName::where('patient_first_name', $firstName)
            ->where('patient_last_name', $lastName)
            ->where('dob', $dob)
            ->where('created_at', $this->lastPhxImportDate)
            ->first();

        if ($row && $row->patient_id) {
            return $row->patient_id;
        }

        return null;
    }
}
