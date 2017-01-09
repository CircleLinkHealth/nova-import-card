<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Problems;


use App\CLH\CCD\ItemLogger\ProblemLog;

trait ConsolidatesProblemInfo
{
    /**
     * Consolidate Problem info from BB Problem and BB Problem Translation sections.
     * Sometimes info is in problem, or translation or both.
     *
     * Overwrite the problem section with the preferred one.
     *
     * @param $ccdProblem
     *
     * @return mixed
     */
    private function consolidateProblemInfo(ProblemLog $ccdProblem)
    {
        $consolidatedProblem = new \stdClass();

        $consolidatedProblem->cons_code = null;
        $consolidatedProblem->cons_code_system = null;
        $consolidatedProblem->cons_code_system_name = null;
        $consolidatedProblem->cons_name = $ccdProblem->name;

        if (!empty($ccdProblem->code)) {
            $consolidatedProblem->cons_code = $ccdProblem->code;
            $consolidatedProblem->cons_code_system = $ccdProblem->code_system;
            $consolidatedProblem->cons_code_system_name = $ccdProblem->code_system_name;
            $consolidatedProblem->cons_name = $ccdProblem->name;

            if (empty($consolidatedProblem->cons_name) && !empty($ccdProblem->translation_name)) {
                $consolidatedProblem->cons_name = $ccdProblem->translation_name;
            }
        } elseif (!empty($ccdProblem->translation_code)) {
            $consolidatedProblem->cons_code = $ccdProblem->translation_code;
            $consolidatedProblem->cons_code_system = $ccdProblem->translation_code_system;
            $consolidatedProblem->cons_code_system_name = $ccdProblem->translation_code_system_name;
            $consolidatedProblem->cons_name = $ccdProblem->translation_name;

            if (empty($consolidatedProblem->cons_name) && !empty($ccdProblem->name)) {
                $consolidatedProblem->cons_name = $ccdProblem->name;
            }
        }

        if (empty($consolidatedProblem->cons_name) && !empty($ccdProblem->reference_title)) {
            $consolidatedProblem->cons_name = $ccdProblem->reference_title;
        }

        return $consolidatedProblem;
    }
}