<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Csv;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Importer\Models\ItemLogs\ProblemCodeLog;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\TabularMedicalRecord;
use App\Services\Eligibility\Entities\Problem as ProblemEntity;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

class TabularMedicalRecordSectionsLogger implements MedicalRecordLogger
{
    /**
     * The Medical Record.
     *
     * @var TabularMedicalRecord
     */
    protected $medicalRecord;

    /**
     * The Practice, if it was passed. Null otherwise.
     *
     * @var \CircleLinkHealth\Customer\Entities\Practice
     */
    protected $practice;

    public function __construct(TabularMedicalRecord $tmr, Practice $practice = null)
    {
        $this->medicalRecord = $tmr;
        $this->practice      = $practice;

        $this->foreignKeys = [
            'vendor_id'           => '1',
            'medical_record_type' => TabularMedicalRecord::class,
            'medical_record_id'   => $tmr->id,
        ];
    }

    /**
     * Log Allergies Section.
     *
     * @return MedicalRecordLogger
     */
    public function logAllergiesSection(): MedicalRecordLogger
    {
        $allergiesToImport = [];

        $allergiesString = $this->medicalRecord->allergies_string;

        if ( ! $allergiesString) {
            return $this;
        }

        foreach (config('importer.allergy_loggers') as $class) {
            $class = app($class);

            if ($class->shouldHandle($allergiesString)) {
                $allergiesToImport = $class->handle($allergiesString);
                break;
            }
        }

        foreach ($allergiesToImport as $allergy) {
            $allergy = AllergyLog::create(
                array_merge([
                    'allergen_name' => trim($allergy),
                ], $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log all Sections.
     */
    public function logAllSections()
    {
        $this
            ->logDemographicsSection()
            ->logAllergiesSection()
            ->logDocumentSection()
            ->logInsuranceSection()
            ->logMedicationsSection()
            ->logProblemsSection()
            ->logProvidersSection();
    }

    /**
     * Log Demographics Section.
     *
     * @return MedicalRecordLogger
     */
    public function logDemographicsSection(): MedicalRecordLogger
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
                'consent_date'  => $this->medicalRecord->consent_date
                    ? $this->medicalRecord->consent_date->format('Y-m-d') > 0
                        ? $this->medicalRecord->consent_date
                        : date('Y-m-d')
                    : date('Y-m-d'),
                'primary_phone'        => $this->medicalRecord->primary_phone,
                'cell_phone'           => $this->medicalRecord->cell_phone,
                'home_phone'           => $this->medicalRecord->home_phone,
                'work_phone'           => $this->medicalRecord->work_phone,
                'email'                => $this->medicalRecord->email,
                'street'               => $this->medicalRecord->address,
                'street2'              => $this->medicalRecord->address2,
                'city'                 => $this->medicalRecord->city,
                'state'                => $this->medicalRecord->state,
                'zip'                  => $this->medicalRecord->zip,
                'preferred_call_times' => $this->medicalRecord->preferred_call_times,
                'preferred_call_days'  => $this->medicalRecord->preferred_call_days,
            ], $this->foreignKeys)
        );

        return $this;
    }

    /**
     * Log Document Section.
     *
     * @return MedicalRecordLogger
     */
    public function logDocumentSection(): MedicalRecordLogger
    {
        return $this;
    }

    /**
     * Log Insurance Section.
     *
     * @return MedicalRecordLogger
     */
    public function logInsuranceSection(): MedicalRecordLogger
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
     * Log Medications Section.
     *
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection(): MedicalRecordLogger
    {
        $medicationsToImport = [];

        $medicationsString = $this->medicalRecord->medications_string;

        if ( ! $medicationsString) {
            return $this;
        }

        foreach (config('importer.medication_loggers') as $class) {
            $class = app($class);

            if ($class->shouldHandle($medicationsString)) {
                $medicationsToImport = $class->handle($medicationsString);
                break;
            }
        }

        foreach ($medicationsToImport as $medication) {
            $medication = MedicationLog::create(
                array_merge($medication, $this->foreignKeys)
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
        $problemsToImport = [];

        $problemsString = $this->medicalRecord->problems_string;

        if ( ! $problemsString) {
            return $this;
        }

        foreach (config('importer.problem_loggers') as $class) {
            $class = app($class);

            if ($class->shouldHandle($problemsString)) {
                $problemsToImport = $class->handle($problemsString);
                break;
            }
        }

        foreach ($problemsToImport as $problem) {
            if (is_a($problem, ProblemEntity::class)) {
                $problem = $problem->toArray();
            }

            $problemLog = ProblemLog::create(
                array_merge([
                    'name'   => $problem['name'],
                    'start'  => $problem['start'],
                    'end'    => $problem['end'],
                    'status' => $problem['status'],
                ], $this->foreignKeys)
            );

            $problemCodeLog = ProblemCodeLog::create([
                'code'                   => $problem['code'],
                'code_system_name'       => $problem['code_system_name'],
                'problem_code_system_id' => $problem['problem_code_system_id'],
                'ccd_problem_log_id'     => $problemLog->id,
            ]);
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
        $delimiter = ' ';

        if (str_contains($this->medicalRecord->provider_name, ',')) {
            $delimiter = ',';
        }

        $name = explode($delimiter, $this->medicalRecord->provider_name);

        $matchProvider = User::ofType('provider')
            ->ofPractice($this->practice->id)
            ->whereFirstName($name[1] ?? '')
            ->whereLastName($name[0] ?? '')
            ->first();

        if ($matchProvider) {
            $provider = ProviderLog::create(array_merge([
                'first_name'          => trim($name[1] ?? ''),
                'last_name'           => trim($name[0] ?? ''),
                'billing_provider_id' => $matchProvider->id,
                'location_id'         => $this->practice->primary_location_id ?? optional($this->practice->locations->first())->id,
            ], $this->foreignKeys));

            return $this;
        }

        $matchProvider = User::ofType('provider')
            ->ofPractice($this->practice->id)
            ->whereFirstName($name[0] ?? '')
            ->whereLastName($name[1] ?? '')
            ->first();

        if ($matchProvider) {
            $provider = ProviderLog::create(array_merge([
                'first_name'          => trim($name[0] ?? ''),
                'last_name'           => trim($name[1] ?? ''),
                'billing_provider_id' => $matchProvider->id,
                'location_id'         => $this->practice->primary_location_id ?? optional($this->practice->locations->first())->id,
            ], $this->foreignKeys));

            return $this;
        }

        $provider = ProviderLog::create(array_merge([
            'first_name'  => trim($name[0] ?? ''),
            'last_name'   => trim($name[1] ?? ''),
            'location_id' => $this->practice->primary_location_id ?? optional($this->practice->locations->first())->id,
        ], $this->foreignKeys));

        return $this;
    }
}
