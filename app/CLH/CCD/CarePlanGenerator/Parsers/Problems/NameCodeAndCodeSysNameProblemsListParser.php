<?php

namespace App\CLH\CCD\CarePlanGenerator\Parsers\Problems;

use App\CLH\Contracts\CCD\ParserWithValidation;
use App\CLH\Contracts\CCD\Validator;

class NameCodeAndCodeSysNameProblemsListParser implements ParserWithValidation
{
    use ConsolidatesProblemInfoTrait;

    public function parse($ccd, Validator $validator)
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