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
        $consolidatedProblem = $ccdProblem;

        if ( !empty($ccdProblem->translation_code) ) {
            $consolidatedProblem->code = $ccdProblem->translation_code;
            $consolidatedProblem->code_system = $ccdProblem->translation_code_system;
            $consolidatedProblem->code_system_name = $ccdProblem->translation_code_system_name;
        }

        if ( empty($consolidatedProblem->name) && !empty($ccdProblem->translation_name) ) {
            $consolidatedProblem->name = $ccdProblem->translation_name;
        }

        return $consolidatedProblem;
    }
}