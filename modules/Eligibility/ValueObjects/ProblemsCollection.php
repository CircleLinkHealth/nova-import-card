<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\ValueObjects;

use CircleLinkHealth\Eligibility\DTO\Problem;

class ProblemsCollection
{
    private $problems;

    /**
     * @return array
     */
    public function getProblemForEligibilityProcessing()
    {
        $problems = [];

        foreach ($this->getProblems() as $problem) {
            if ( ! array_key_exists('events', $problem)) {
                $problems[] = Problem::create(['code' => $problem['code'], 'code_system_name' => $problem['codeset'], 'name' => $problem['name']]);

                continue;
            }

            foreach ($problem['events'] as $event) {
                if ( ! array_key_exists('diagnoses', $event)) {
                    continue;
                }

                foreach ($event['diagnoses'] as $diagnosis) {
                    if ( ! array_key_exists('code', $diagnosis)) {
                        continue;
                    }

                    $problems[] = Problem::create(['code' => $diagnosis['code'], 'code_system_name' => $diagnosis['codeset'], 'name' => $diagnosis['name']]);

                    continue 3;
                }
            }
        }

        return $problems;
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
