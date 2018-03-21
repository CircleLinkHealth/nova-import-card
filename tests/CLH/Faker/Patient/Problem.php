<?php


namespace CLH\Faker\Patient;


use App\User;

class Problem
{

    protected $ccdProblems = array();

    protected $cpmProblems = array();

    /**
     * returns `ccd_problem`
     *
     * @param bool $withCodes
     * @param null $name
     *
     * @return int
     */
    public function problem($withCodes = true, $name = null){

        //see ccd problem attributes


        $ccd_problem = 0;

        return $ccd_problem;
    }


    /**
     * returns array of `ccd_problem` for sample patients
     *
     * @param bool $withCodes
     */
    public function problemSet($withCodes = true){

    }

    /**
     *
     * attaches problems to user
     *
     * @param User $patient
     */
    public function attachProblemSet(User $patient){

        //Search for attach problem on User model

    }
}