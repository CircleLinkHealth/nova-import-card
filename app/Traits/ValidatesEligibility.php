<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 04/11/2018
 * Time: 1:09 AM
 */

namespace App\Traits;


use App\Rules\EligibilityPhones;
use App\Rules\EligibilityProblems;
use Validator;

trait ValidatesEligibility
{
    public function validateRow($row)
    {
        if (array_key_exists('patient_id', $row)) {
            $row['mrn'] = $row['patient_id'];
        }
        if (array_key_exists('date_of_birth', $row)){
            $row['dob'] = $row['date_of_birth'];
        }
        $row = $this->transformProblems($row);
        $row = $this->transformPhones($row);

        return $this->validatePatient($row);
    }

    private function transformProblems(Array $row)
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

    private function transformPhones(Array $row)
    {
        $row['phones'] = [
            'primary_phone' => array_key_exists('primary_phone', $row)
                ? $row['primary_phone']
                : null,
            'home_phone'    => array_key_exists('home_phone', $row)
                ? $row['home_phone']
                : null,
            'cell_phone'    => array_key_exists('cell_phone', $row)
                ? $row['cell_phone']
                : null,
            'other_phone'   => array_key_exists('other_phone', $row)
                ? $row['other_phone']
                : null,
        ];

        return $row;
    }

    public function validateJsonStructure($row){

        $toValidate = [];
        $rules      = [];

        foreach (array_keys($row) as $key) {
            $toValidate[$key] = $key;
        }

        foreach ($this->validJsonKeys() as $name) {
            $rules[$name] = 'required|filled|same:' . $name;
        }

        return Validator::make($toValidate, $rules);

    }

    public function validatePatient(Array $array)
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

    public function validJsonKeys(): array
    {
        return [
            "email",
            "language",
            "gender",
            "patient_id",
            "last_name",
            "first_name",
            "middle_name",
            "date_of_birth",
            "address_line_1",
            "address_line_2",
            "city",
            "state",
            "postal_code",
            "primary_phone",
            "cell_phone",
            "preferred_provider",
            "last_visit",
            "insurance_plans",
            "problems",
            "medications",
            "allergies",
        ];
    }


}