<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Csv;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordLogger;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProviderLog;
use CircleLinkHealth\SharedModels\Entities\TabularMedicalRecord;
use App\Search\ProviderByName;
use CircleLinkHealth\Eligibility\Entities\Problem as ProblemEntity;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemCodeLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;
use CircleLinkHealth\SharedModels\Entities\AllergyLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationLog;

class TabularMedicalRecordSectionsLogger implements MedicalRecordLogger
{
    /**
     * The Medical Record.
     *
     * @var \CircleLinkHealth\SharedModels\Entities\TabularMedicalRecord
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
            'medical_record_type' => TabularMedicalRecord::class,
            'medical_record_id'   => $tmr->id,
        ];
    }

    /**
     * Log Allergies Section.
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
            $allergy = AllergyLog::updateOrCreate(
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
     */
    public function logDemographicsSection(): MedicalRecordLogger
    {
        $saved = DemographicsLog::updateOrCreate(
            array_merge([
                'first_name'    => $this->medicalRecord->first_name,
                'last_name'     => $this->medicalRecord->last_name,
                'dob'           => $this->medicalRecord->dob,
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
     */
    public function logDocumentSection(): MedicalRecordLogger
    {
        return $this;
    }

    /**
     * Log Insurance Section.
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
            $insurance = InsuranceLog::updateOrCreate(array_merge([
                'name'     => $insurance,
                'import'   => true,
            ], $this->foreignKeys));
        }

        return $this;
    }

    /**
     * Log Medications Section.
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
            $medication = MedicationLog::updateOrCreate(
                array_merge($medication, $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Problems Section.
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

            $problemLog = ProblemLog::updateOrCreate(
                array_merge([
                    'name'   => $problem['name'],
                    'start'  => $problem['start'],
                    'end'    => $problem['end'],
                    'status' => $problem['status'],
                ], $this->foreignKeys)
            );

            try {
                $problemCodeLog = ProblemCodeLog::updateOrCreate([
                    'code'                   => $problem['code'],
                    'code_system_name'       => $problem['code_system_name'],
                    'problem_code_system_id' => $problem['problem_code_system_id'],
                    'ccd_problem_log_id'     => $problemLog->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Error at '.__CLASS__.':'.__LINE__.' | defined vars:'.print_r(get_defined_vars()));
            }
        }

        return $this;
    }

    /**
     * Log Providers Section.
     */
    public function logProvidersSection(): MedicalRecordLogger
    {
        $searchProvider = ProviderByName::first($this->medicalRecord->provider_name);

        if ($searchProvider) {
            $data['provider_id'] = $data['billing_provider_id'] = $searchProvider->id;
            $data['practice_id'] = $searchProvider->program_id;
            $data['location_id'] = optional($searchProvider->loadMissing('locations')->locations->first())->id;
        }

        if ($searchProvider) {
            ProviderLog::updateOrCreate(
                array_merge([
                    'first_name' => $searchProvider->first_name,
                    'last_name'  => $searchProvider->last_name,
                ], $this->foreignKeys),
                array_merge($data, $this->foreignKeys)
            );

            return $this;
        }

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
            $provider = ProviderLog::updateOrCreate(array_merge([
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
            $provider = ProviderLog::updateOrCreate(array_merge([
                'first_name'          => trim($name[0] ?? ''),
                'last_name'           => trim($name[1] ?? ''),
                'billing_provider_id' => $matchProvider->id,
                'location_id'         => $this->practice->primary_location_id ?? optional($this->practice->locations->first())->id,
            ], $this->foreignKeys));

            return $this;
        }

        $provider = ProviderLog::updateOrCreate(array_merge([
            'first_name'  => trim($name[0] ?? ''),
            'last_name'   => trim($name[1] ?? ''),
            'location_id' => $this->practice->primary_location_id ?? optional(optional($this->practice)->locations)->first()->id ?? null,
        ], $this->foreignKeys));

        return $this;
    }
}
