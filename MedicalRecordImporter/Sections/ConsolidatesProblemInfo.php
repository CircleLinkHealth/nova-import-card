<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections;

trait ConsolidatesProblemInfo
{
    /**
     * Consolidate Problem info from BB Problem and BB Problem Translation sections.
     * Sometimes info is in problem, or translation or both.
     *
     * Overwrite the problem section with the preferred one.
     *
     * @param object $problemLog
     *
     * @return mixed
     */
    private function consolidateProblemInfo(object $problemLog)
    {
        $consolidatedProblem = new \stdClass();

        $consolidatedProblem->cons_code             = null;
        $consolidatedProblem->cons_code_system      = null;
        $consolidatedProblem->cons_code_system_name = null;
        $consolidatedProblem->cons_name             = $problemLog->name;
        $consolidatedProblem->status                = $problemLog->status ?? null;
        $consolidatedProblem->start                = $problemLog->start ?? null;
        $consolidatedProblem->end                = $problemLog->end ?? null;

        if ( ! empty($problemLog->code)) {
            $consolidatedProblem->cons_code             = $problemLog->code;
            $consolidatedProblem->cons_code_system      = $problemLog->code_system;
            $consolidatedProblem->cons_code_system_name = $problemLog->code_system_name;
            $consolidatedProblem->cons_name             = $problemLog->name;

            if (empty($consolidatedProblem->cons_name) && ! empty($problemLog->translation_name)) {
                $consolidatedProblem->cons_name = $problemLog->translation_name;
            }
        } elseif ( ! empty($problemLog->translation_code)) {
            $consolidatedProblem->cons_code             = $problemLog->translation_code;
            $consolidatedProblem->cons_code_system      = $problemLog->translation_code_system;
            $consolidatedProblem->cons_code_system_name = $problemLog->translation_code_system_name;
            $consolidatedProblem->cons_name             = $problemLog->translation_name;

            if (empty($consolidatedProblem->cons_name) && ! empty($problemLog->name)) {
                $consolidatedProblem->cons_name = $problemLog->name;
            }
        }
        
        if (!$this->isSensible($consolidatedProblem->cons_name)) {
            $consolidatedProblem->cons_name = null;
        }

        if (empty($consolidatedProblem->cons_name) && ! empty($problemLog->reference_title) && $this->isSensible($problemLog->reference_title)) {
            $consolidatedProblem->cons_name = $problemLog->reference_title;
        }

        return $consolidatedProblem;
    }
    
    /**
     * Determine whether the problem name is a valid one.
     *
     * @param $problemName
     *
     * @return bool
     */
    private function isSensible($problemName) :bool {
        return !empty($problemName) && ! in_array(strtolower(trim($problemName)), $this->invalidNames());
    }
    
    /**
     * Invalid problem names. Use small caps only.
     *
     * @return array
     */
    private function invalidNames() {
        return ['condition'];
    }
}
