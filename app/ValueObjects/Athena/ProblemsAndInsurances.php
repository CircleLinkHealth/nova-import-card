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


    public function getInsurancesForEligibilityCheck()
    {
        $insurances = [];

        foreach ($this->insurances as $insurance) {
            $insurance['type'] = $insurance['insurancetype'] ?? $insurance['insuranceplanname'];
            $insurances[]      = $insurance;
        }

        return $insurances;
    }

    /**
     * @return array
     */
    public function getProblemCodes()
    {
        $codes = [];

        foreach ($this->getProblems() as $problem) {
            if (! array_key_exists('events', $problem)) {
                $codes[] = \App\Services\Eligibility\Entities\Problem::create(['code' => $problem['code']]);

                continue;
            }

            foreach ($problem['events'] as $event) {
                if (! array_key_exists('diagnoses', $event)) {
                    continue;
                }

                foreach ($event['diagnoses'] as $diagnosis) {
                    if (! array_key_exists('code', $diagnosis)) {
                        continue;
                    }

                    $codes[] = \App\Services\Eligibility\Entities\Problem::create(['code' => $diagnosis['code']]);

                    continue 3;
                }
            }
        }

        return $codes;
    }
}
