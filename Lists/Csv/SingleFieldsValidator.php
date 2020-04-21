<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Lists\Csv;

class SingleFieldsValidator extends BaseValidator
{
    /**
     * This method returns any fields that are required to be present in each row.
     *
     * @todo: Michalis commented out a few fields below because they were not included in Arnot's lists and they were causing issues. These fields should not be "required" because we can process without them
     *
     * @return array
     */
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
            //            'cell_phone',
            //            'home_phone',
            //            'other_phone',
            'primary_phone',
            //            'email',
            'street',
            //            'street2',
            'city',
            'state',
            'zip',
            'primary_insurance',
            //            'secondary_insurance',
            //            'tertiary_insurance',
            'last_encounter',
            'problems_string',
            //            'allergies_string',
            //            'medications_string',
        ];
    }

    /**
     * Validates an array of column names from a CSV that is uploaded to be processed for eligibility.
     * Returns false if there's no errors, and an array of errors if errors are found.
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

        $this->validator = \Validator::make($toValidate, $rules);

        return $this->validator->passes()
            ? true
            : $this->validator->errors()->all();
    }
}
