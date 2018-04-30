<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/30/18
 * Time: 1:31 PM
 */

namespace App\Services\Eligibility\Csv\Validators\PatientList;

class SingleFieldsValidator implements PatientListValidator
{
    /**
     * @var array
     */
    private $columnNames = [];

    private $validator;

    public function isValid()
    {
        return $this->validate() === true;
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
        if ( ! $this->validator) {
            $toValidate = [];
            $rules      = [];

            foreach ($this->getColumnNames() as $cn) {
                $toValidate[$cn] = $cn;
            }

            foreach ($this->required() as $name) {
                $rules[$name] = 'required|filled|same:' . $name;
            }

            $this->validator = \Validator::make($toValidate, $rules);
        }

        return $this->validator->passes()
            ? true
            : $this->validator->errors()->all();
    }

    /**
     * @return array
     */
    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    /**
     * @param array $columnNames
     *
     * @return SingleFieldsValidator
     */
    public function setColumnNames(array $columnNames)
    {
        $this->columnNames = $columnNames;

        return $this;
    }

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
            'email',
            'street',
            'street2',
            'city',
            'state',
            'zip',
            'primary_insurance',
            'secondary_insurance',
            'tertiary_insurance',
            'last_encounter',
            'problems_string',
            'allergies_string',
            'medications_string',
        ];
    }

    public function errors()
    {
        return $this->validate() === true
            ? null
            : $this->validate();
    }
}