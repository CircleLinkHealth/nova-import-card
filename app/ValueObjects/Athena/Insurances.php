<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\Athena;

class Insurances
{
    private $insurances;

    /**
     * @return mixed
     */
    public function getInsurances()
    {
        return $this->insurances;
    }

    public function getInsurancesForEligibilityCheck()
    {
        $insurances = [];

        foreach ($this->insurances as $insurance) {
            $insurances[] = json_encode($insurance);
        }

        return $insurances;
    }

    /**
     * @param mixed $insurances
     */
    public function setInsurances($insurances)
    {
        $this->insurances = $insurances;
    }
}
