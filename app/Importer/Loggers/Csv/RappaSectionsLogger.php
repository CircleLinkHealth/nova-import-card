<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Csv;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use CircleLinkHealth\CarePlanModels\Entities\AllergyLog;
use App\Importer\Models\ItemLogs\InsuranceLog;
use CircleLinkHealth\CarePlanModels\Entities\MedicationLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\TabularMedicalRecord;
use App\Models\PatientData\Rappa\RappaData;
use App\Models\PatientData\Rappa\RappaInsAllergy;
use App\Models\PatientData\Rappa\RappaName;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

class RappaSectionsLogger extends TabularMedicalRecordSectionsLogger
{
    public function __construct(TabularMedicalRecord $tmr, Practice $practice = null)
    {
        parent::__construct($tmr, $practice);

        $rappaNames = RappaName::wherePatientId($this->medicalRecord->mrn)
            ->get()
            ->keyBy('patient_id');

        $rappaInsAllergies = RappaInsAllergy::wherePatientId($this->medicalRecord->mrn)
            ->get()
            ->keyBy('patient_id');

        $merged = $rappaInsAllergies->map(function ($rappaInsAllergy) use ($rappaNames) {
            if ($name = $rappaNames->get($rappaInsAllergy->patient_id)) {
                return collect($name)->merge($rappaInsAllergy);
            }

            return collect($rappaInsAllergy);
        });

        $patientList = $merged->map(function ($patient) {
            $data = RappaData::where('patient_id', '=', $patient->get('patient_id'))->get();

            $patient->put('medications', collect());
            $patient->put('problems', collect());

            foreach ($data as $d) {
                if ($d['medication'] && ! $patient['medications']->contains($d['medication'])) {
                    $patient['medications']->push($d['medication']);
                }

                if ($d['condition'] && ! $patient['problems']->contains($d['condition'])) {
                    $patient['problems']->push($d['condition']);
                }

                if ( ! $patient->contains($d['last_name'])) {
                    $patient->put('last_name', $d['last_name']);
                }

                if ( ! $patient->contains($d['first_name'])) {
                    $patient->put('first_name', $d['first_name']);
                }
            }

            return $patient;
        });

        $this->rappaPatient = $patientList->first();

        $this->updateTMR();
    }

    /**
     * Log Allergies Section.
     *
     * @return MedicalRecordLogger
     */
    public function logAllergiesSection(): MedicalRecordLogger
    {
        $allergies = RappaInsAllergy::wherePatientId($this->medicalRecord->mrn)
            ->get()
            ->pluck('allergy')
            ->unique()
            ->values();

        foreach ($allergies as $allergy) {
            $allergyLog = AllergyLog::create(
                array_merge([
                    'allergen_name' => ucfirst(strtolower($allergy)),
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Insurance Section.
     *
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

        if ('No Secondary Plan' == $insurances->secondary_insurance) {
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
     *
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection(): MedicalRecordLogger
    {
        foreach ($this->rappaPatient->get('medications') as $medication) {
            $medicationLog = MedicationLog::updateOrCreate(
                array_merge([
                    'reference_title'  => ucfirst(strtolower($medication)),
                    'product_name'     => ucfirst(strtolower($medication)),
                    'translation_name' => ucfirst(strtolower($medication)),
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Problems Section.
     *
     * @return MedicalRecordLogger
     */
    public function logProblemsSection(): MedicalRecordLogger
    {
        foreach ($this->rappaPatient->get('problems') as $problem) {
            $problemLog = ProblemLog::updateOrCreate(
                array_merge([
                    'name' => $problem,
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Providers Section.
     *
     * @return MedicalRecordLogger
     */
    public function logProvidersSection(): MedicalRecordLogger
    {
        $name = RappaData::wherePatientId($this->medicalRecord->mrn)
            ->first();

        if ($name) {
            $providerLastName = $name->provider;
        } else {
            $name = RappaInsAllergy::wherePatientId($this->medicalRecord->mrn)
                ->first();

            if ($name) {
                if (empty($name->provider)) {
                    return $this;
                }

                $providerLastName = explode(',', $name->provider)[0];
            }
        }

        $user = User::ofType('provider')
            ->whereProgramId($this->practice->id)
            ->whereLastName($providerLastName)
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

    public function updateTMR()
    {
        $this->medicalRecord->update([
            'mrn'        => $this->rappaPatient->get('patient_id'),
            'first_name' => $this->rappaPatient->get('first_name'),
            'last_name'  => $this->rappaPatient->get('last_name'),

            'medications_string' => implode(',', $this->rappaPatient->get('medications')->all()),
            'problems_string'    => implode(',', $this->rappaPatient->get('problems')->all()),

            'dob' => $this->rappaPatient->get('dob') ?? null,

            //            'gender' => $this->rappaPatient->get(''),

            'provider_name' => $this->rappaPatient->get('provider'),

            'primary_phone' => $this->rappaPatient->get('primary_phone'),
            'home_phone'    => $this->rappaPatient->get('home_phone'),
            'work_phone'    => $this->rappaPatient->get('work_phone'),
            'email'         => $this->rappaPatient->get('email'),

            'address'  => $this->rappaPatient->get('address_1'),
            'address2' => $this->rappaPatient->get('address_2'),
            'city'     => $this->rappaPatient->get('city'),
            'state'    => $this->rappaPatient->get('state'),
            'zip'      => $this->rappaPatient->get('zip'),

            'primary_insurance'   => $this->rappaPatient->get('primary_insurance'),
            'secondary_insurance' => $this->rappaPatient->get('secondary_insurance'),
        ]);
    }
}
