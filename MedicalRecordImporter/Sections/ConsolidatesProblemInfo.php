<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;

trait ConsolidatesProblemInfo
{
    /**
     * Consolidate Problem info from BB Problem and BB Problem Translation sections.
     * Sometimes info is in problem, or translation or both.
     *
     * Overwrite the problem section with the preferred one.
     *
     * @param ProblemLog $problemLog
     *
     * @return mixed
     */
    private function consolidateProblemInfo(ProblemLog $problemLog)
    {
        $consolidatedProblem = new \stdClass();

        $consolidatedProblem->cons_code             = null;
        $consolidatedProblem->cons_code_system      = null;
        $consolidatedProblem->cons_code_system_name = null;
        $consolidatedProblem->cons_name             = $problemLog->name;

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

        if (empty($consolidatedProblem->cons_name) && ! empty($problemLog->reference_title)) {
            $consolidatedProblem->cons_name = $problemLog->reference_title;
        }

        return $consolidatedProblem;
    }
}
