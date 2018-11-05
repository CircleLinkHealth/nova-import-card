<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 04/11/2018
 * Time: 1:09 AM
 */

namespace App\Traits;


use Illuminate\Support\Collection;
use Validator;

trait ValidatesEligibilityCsv
{
    public function validateRow($row){
        //validate row
        //return validator

        return $this->validate($row);
    }

    public function determineFieldFormat(){
        //problem string, is it JSON? Is it a Problem class? how is it represented in the CSV? Test with ops csv excel report.
        //i think its json for sure, because the system converts it to that when we get here.
    }

    //to perform extra validations?

    //to perform validation for the whole csv?
    public function validateCsv(){

    }

    public function validate(Collection $collection){

        return Validator::make($collection->all(), [
            'patient_id' => 'required',
            'last_name' => 'required|alpha_num',
            'first_name' => 'required|alpha_num',
            'date_of_birth' => 'required|date',
            'problems' => ['required', function ($attribute, $value, $fail) {
                if (count($value) < 1) {
                    $fail($attribute . 'field must contain at least 1 problem.');
                }
            }],
            'primary_phone' => "phone:us|required_if:cell_phone,==,null,",
            'cell_phone' => "phone:us|required_if:primary_phone,==,null,",
        ]);
    }


}