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

        if ( !empty($ccdProblem->translation->code) ) {
            $consolidatedProblem->code = $ccdProblem->translation->code;
            $consolidatedProblem->code_system = $ccdProblem->translation->code_system;
            $consolidatedProblem->code_system_name = $ccdProblem->translation->code_system_name;
        }

        if ( empty($consolidatedProblem->name) && !empty($ccdProblem->translation->name) ) {
            $consolidatedProblem->name = $ccdProblem->translation->name;
        }

        return $consolidatedProblem;
    }
}