<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 04/11/2018
 * Time: 1:09 AM
 */

namespace App\Traits;


use App\Rules\CsvEligibilityPhones;
use App\Rules\CsvEligibilityProblems;
use Validator;

trait ValidatesEligibilityCsv
{
    public function validateRow($row)
    {
        $row = $this->transformProblems($row);
        $row = $this->transformPhones($row);

        return $this->validate($row);
    }

    private function transformProblems(Array $row)
    {

        if (is_json($row['problems_string'])) {
            $problems               = json_decode($row['problems_string'])->Problems;
            $row['problems_string'] = [];
            foreach ($problems as $problem) {
                $row['problems_string'][] = [
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

    //to perform validation for the whole csv?
    public function validateCsv()
    {

    }

    private function validate(Array $array)
    {
        return Validator::make($array, [
            'mrn'             => 'required',
            'last_name'       => 'required|alpha_num',
            'first_name'      => 'required|alpha_num',
            'dob'             => 'required|date',
            'problems_string' => ['required', new CsvEligibilityProblems()],
            'phones'          => ['required', new CsvEligibilityPhones()],
        ]);

    }


}