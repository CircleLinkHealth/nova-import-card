<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CarePlanConditions implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //value is a collection of the ccdProblems relationship
        if ($value->isEmpty()){
            return false;
        }

        $cpmProblems = $value->count();
        $bhiProblems = $value->where('cpmProblem.is_behavioral', true)->count();

//        $cpmProblemx = $value->where('cpmProblem', function ($p){
//            $p->where('id', '!=', 23);
//        });
//
//        foreach ($value as $ccdProblem){
//            if ($ccdProblem->isBehavioral()){
//                $bhiProblems += 1;
//                continue;
//            }elseif ($ccdProblem->cpmProblem()->first()){
//                $cpmProblems += 1;
//            }
//        }
        return $cpmProblems >= 2 || $bhiProblems >= 1;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Care Plan must have two CPM problems, or one BHI problem.';
    }
}
