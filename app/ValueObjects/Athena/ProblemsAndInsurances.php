<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/26/2017
 * Time: 3:16 PM
 */

namespace App\ValueObjects\Athena;


class ProblemsAndInsurances
{
    private $insurances;
    private $problems;

    /**
     * @return mixed
     */
    public function getInsurances()
    {
        return $this->insurances;
    }

    /**
     * @param mixed $insurances
     */
    public function setInsurances($insurances)
    {
        $this->insurances = $insurances;
    }

    /**
     * @return mixed
     */
    public function getProblems()
    {
        return $this->problems;
    }

    /**
     * @param mixed $problems
     */
    public function setProblems($problems)
    {
        $this->problems = $problems;
    }
}