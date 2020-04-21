<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility;

use CircleLinkHealth\Eligibility\Entities\CsvPatientList;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Rules\EligibilityPhones;
use CircleLinkHealth\Eligibility\Rules\EligibilityProblems;
use Illuminate\Support\Collection;
use Validator;

trait ValidatesEligibility
{
    public function addAtLeastOnePhoneRule(&$rules)
    {
        $rules['primary_phone'] = 'required_without_all:cell_phone,work_phone,home_phone';
        $rules['cell_phone']    = 'required_without_all:primary_phone,work_phone,home_phone';
        $rules['work_phone']    = 'required_without_all:primary_phone,cell_phone,home_phone';
        $rules['home_phone']    = 'required_without_all:primary_phone,cell_phone,work_phone';
    }

    /**
     * @return array
     */
    public function getCsvStructureErrors(EligibilityJob $job)
    {
        $csvPatientList = new CsvPatientList(collect([$job->data]));
        $isValid        = $csvPatientList->guessValidatorAndValidate() ?? null;

        $errors = [];
        if ( ! $isValid) {
            $errors[] = 'structure';
        }
        $errors = array_merge($this->validateRow($job->data)->errors()->keys(), $errors);
        $this->saveErrorsOnEligibilityJob($job, collect($errors));

        return $errors;
    }

    public function saveErrorsOnEligibilityJob(EligibilityJob $job, Collection $errors)
    {
        //check keys and update job

        if ($errors->isNotEmpty() && ! (1 == $errors->count() && 'structure' == $errors->first())) {
            $job->invalid_data = true;
        }
        //check for invalid data
        $job->invalid_structure  = $errors->contains('structure');
        $job->invalid_mrn        = $errors->contains('mrn');
        $job->invalid_first_name = $errors->contains('first_name');
        $job->invalid_last_name  = $errors->contains('last_name');
        $job->invalid_dob        = $errors->contains('dob');
        $job->invalid_problems   = $errors->contains('problems');
        $job->invalid_phones     = $errors->contains('phones');

        $job->save();
    }

    public function validateJsonStructure($row)
    {
        $toValidate = [];
        $rules      = [];

        foreach (array_keys($row) as $key) {
            $toValidate[$key] = $key;
        }

        foreach ($this->validJsonKeys() as $name) {
            $rules[$name] = 'required|filled|same:'.$name;
        }

        if ( ! $this->filterLastEncounter) {
            unset($rules['last_visit']);
        }

        $this->addAtLeastOnePhoneRule($rules);

        return Validator::make($toValidate, $rules);
    }

    public function validatePatient(array $array)
    {
        return Validator::make($array, [
            'mrn'        => 'required',
            'last_name'  => 'required|alpha_num',
            'first_name' => 'required|alpha_num',
            'dob'        => 'required|date',
            'problems'   => ['required', new EligibilityProblems()],
            'phones'     => ['required', new EligibilityPhones()],
        ]);
    }

    public function validateRow($row)
    {
        if (array_key_exists('patient_id', $row)) {
            $row['mrn'] = $row['patient_id'];
        }
        if (array_key_exists('date_of_birth', $row)) {
            $row['dob'] = $row['date_of_birth'];
        }
        $row = $this->transformProblems($row);
        $row = $this->transformPhones($row);

        return $this->validatePatient($row);
    }

    public function validJsonKeys(): array
    {
        return [
            'email',
            'language',
            'gender',
            'patient_id',
            'last_name',
            'first_name',
            'middle_name',
            'date_of_birth',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'postal_code',
            'preferred_provider',
            'last_visit',
            'insurance_plans',
            'problems',
            'medications',
            'allergies',
        ];
    }

    private function transformPhones(array $row)
    {
        $row['phones'] = [
            'primary_phone' => array_key_exists('primary_phone', $row)
                ? $row['primary_phone']
                : null,
            'home_phone' => array_key_exists('home_phone', $row)
                ? $row['home_phone']
                : null,
            'cell_phone' => array_key_exists('cell_phone', $row)
                ? $row['cell_phone']
                : null,
            'other_phone' => array_key_exists('other_phone', $row)
                ? $row['other_phone']
                : null,
        ];

        return $row;
    }

    private function transformProblems(array $row)
    {
        if (array_key_exists('problems_string', $row) && is_json($row['problems_string'])) {
            $problems               = json_decode($row['problems_string'])->Problems;
            $row['problems_string'] = [];
            foreach ($problems as $problem) {
                $row['problems'][] = [
                    'Name' => $problem->Name,
                    'Code' => $problem->Code,
                ];
            }
        }

        return $row;
    }
}
