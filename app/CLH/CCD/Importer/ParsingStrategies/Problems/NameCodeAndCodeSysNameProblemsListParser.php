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

            $problemsList .= "\n\n";

            //quick fix to display snomed ct in middletown
            $codeSystemName = function() use ($problemCodes) {
                return empty($problem = $problemCodes->code_system_name)
                    ? empty($problemCodes->code_system)
                        ?: ($problemCodes->code_system == '2.16.840.1.113883.6.96') ? 'SNOMED CT' : ''
                    : $problem;
            };

            $problemsList .= ucwords( strtolower( $problemCodes->name ) ) . ', '
                . strtoupper( $codeSystemName() ) . ', '
                . $problemCodes->code . ";";
        }

        return $problemsList;
    }
}