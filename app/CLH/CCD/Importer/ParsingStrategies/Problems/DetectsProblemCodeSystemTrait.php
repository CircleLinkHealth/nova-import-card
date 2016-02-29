<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Problems;


use App\CLH\CCD\Importer\SnomedToICD10Map;

trait DetectsProblemCodeSystemTrait
{
    public function detectProblemCodeSystem($ccdProblem)
    {
        $code = $ccdProblem->code;

        if ( empty($code) ) return;

        $codeFirstChar = substr( $code, 0, 1 );
        $codeSecondAndThridChars = substr( $code, 1, 2 );
        $codeRemainingChars = substr( $code, 1 );
        //Get only alphanumeric chars (omit dots, or other number separators)
        $codeLength = strlen( preg_replace( '/[^a-z_\-0-9]/i', '', $code ) );
        $icd10Code = SnomedToICD10Map::where( 'icd_10_code', $code )->get();

        //ICD 10
        if (
            (($codeLength >= 3 && $codeLength <= 7)
                && (ctype_alpha( $codeFirstChar ))
                && (is_numeric( $codeSecondAndThridChars )))
            || count( $icd10Code ) > 0
        ) {
            $ccdProblem->code_system_name = 'ICD-10';
            $ccdProblem->code_system = '2.16.840.1.113883.6.3';
            $ccdProblem->code = $code;

            return $ccdProblem;
        }

        //ICD 9
        if (
            ($codeLength >= 3 && $codeLength <= 5)
            && (is_numeric( $codeFirstChar ) || in_array( $codeFirstChar, ['E', 'V'] ))
            && (is_numeric( $codeRemainingChars ))
        ) {
            $ccdProblem->code_system_name = 'ICD-9';
            $ccdProblem->code_system = '2.16.840.1.113883.6.103';
            $ccdProblem->code = $code;

            return $ccdProblem;
        }

        //Snomed
        if ( is_numeric( $code ) ) {
            $ccdProblem->code_system_name = 'SNOMED CT';
            $ccdProblem->code_system = '2.16.840.1.113883.6.96';
            $ccdProblem->code = $code;

            return $ccdProblem;
        }

        return $ccdProblem;
    }
}