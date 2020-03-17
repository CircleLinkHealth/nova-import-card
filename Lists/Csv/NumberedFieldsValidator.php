<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Lists\Csv;

class NumberedFieldsValidator extends BaseValidator
{
    public function required()
    {
        return [
            'mrn',
            'last_name',
            'first_name',
            'dob',
            'gender',
            'lang',
            'referring_provider_name',
            'cell_phone',
            'home_phone',
            'other_phone',
            'primary_phone',
//            'email',
            'street',
            'street2',
            'city',
            'state',
            'zip',
            'primary_insurance',
            'secondary_insurance',
            'tertiary_insurance',
            'last_encounter',
        ];
    }

    /**
     * Validates an array of column names from a CSV that is uploaded to be processed for eligibility.
     * Returns false if there's no errors, and an array of errors if errors are found.
     *
     * @param array $columnNames
     *
     * @return array|bool
     */
    public function validate()
    {
        $toValidate = [];
        $rules      = [];

        foreach ($this->getColumnNames() as $cn) {
            $toValidate[$cn] = $cn;
        }

        foreach ($this->required() as $name) {
            $rules[$name] = 'required|filled|same:'.$name;
        }

        $rules['problem_1'] = 'required|filled|same:problem_1';
        //if pcm or bhi, only one problem is needed.
        //$rules['problem_2'] = 'required|filled|same:problem_2';

        $this->validator = \Validator::make($toValidate, $rules);

        return $this->validator->passes()
            ? true
            : $this->validator->errors()->all();
    }
}
