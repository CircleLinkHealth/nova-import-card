<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Problems;


trait ConsolidatesProblemInfoTrait
{
    /**
     * Consolidate Problem info from BB Problem and BB Problem Translation sections.
     * Sometimes info is in problem, or translation or both.
     *
     * Overwrite the problem section with the preferred one.
     *
     * @param $ccdProblem
     * @return mixed
     */
    private function consolidateProblemInfo($ccdProblem)
    {
        $consolidatedProblem = new \stdClass();

        if ( !empty($ccdProblem->translation_code) ) {
            $consolidatedProblem->cons_code = $ccdProblem->translation_code;
            $consolidatedProblem->cons_code_system = $ccdProblem->translation_code_system;
            $consolidatedProblem->cons_code_system_name = $ccdProblem->translation_code_system_name;
        }

        if ( empty($consolidatedProblem->name) && !empty($ccdProblem->translation_name) ) {
            $consolidatedProblem->cons_name = $ccdProblem->translation_name;
        }

        return $consolidatedProblem;
    }
}