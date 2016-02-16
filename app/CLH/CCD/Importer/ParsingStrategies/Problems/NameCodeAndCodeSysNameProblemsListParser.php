<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Problems;

use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;

class NameCodeAndCodeSysNameProblemsListParser implements ParsingStrategy
{
    use ConsolidatesProblemInfoTrait;

    public function parse($ccd, ValidationStrategy $validator = null)
    {
        $problemsSection = $ccd->problems;

        $problemsList = '';

        foreach ( $problemsSection as $ccdProblem ) {
            if ( !$validator->validate( $ccdProblem ) ) continue;

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $this->consolidateProblemInfo( $ccdProblem );

            /**
             * If all fields are empty, then skip this problem to avoid having ,,; on the problems list
             */
            if ( empty($problemCodes->name)
                && empty($problemCodes->code_system_name)
                && empty($problemCodes->code)
            ) continue;

            $problemsList .= ucwords( strtolower( $problemCodes->name ) ) . ', '
                . strtoupper( $problemCodes->code_system_name ) . ', '
                . $problemCodes->code . ";\n\n";
        }

        return $problemsList;
    }
}