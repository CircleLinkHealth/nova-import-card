<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Problems;


use App\CLH\CCD\Importer\CPMProblem;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\CLH\CCD\Importer\SnomedToICD10Map;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;

class ProblemsToMonitorParser implements ParsingStrategy
{
    use ConsolidatesProblemInfoTrait;

    public function parse($ccd, ValidationStrategy $validator = null)
    {
        $problemsSection = $ccd->problems;

        $cpmProblems = CPMProblem::all();

        $problemsToActivate = [];

        foreach ( $problemsSection as $ccdProblem ) {

            if ( !$validator->validate( $ccdProblem ) ) continue;

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $this->consolidateProblemInfo( $ccdProblem );

            if ( empty($problemCodes->code_system_name) && empty($problemCodes->code_system) ) continue;

            /*
             * ICD-9 Check
             */
            if ( in_array( $problemCodes->code_system_name, ['ICD-9', 'ICD9'] ) || $problemCodes->code_system == '2.16.840.1.113883.6.103' ) {
                foreach ( $cpmProblems as $cpmProblem ) {
                    if ( $problemCodes->code >= $cpmProblem->icd9from
                        && $problemCodes->code <= $cpmProblem->icd9to
                    ) {
                        array_push( $problemsToActivate, $cpmProblem->name );
                        continue 2;
                    }
                }
            }

            /*
                 * SNOMED Check
                 */
            if ( in_array( $problemCodes->code_system_name, ['SNOMED CT'] ) || $problemCodes->code_system == '2.16.840.1.113883.6.96' ) {
                $potentialICD10List = SnomedToCpmIcdMap::whereSnomedCode( $problemCodes->code )->lists( 'icd_10_code' );

                if ( !empty($potentialICD10List[ 0 ]) ) {
                    $problemCodes->code_system_name = 'ICD-10';
                    $problemCodes->code_system = '2.16.840.1.113883.6.3';
                    $problemCodes->code = $potentialICD10List[ 0 ];
                }
            }

            /*
             * ICD-10 Check
             */
            if ( in_array( $problemCodes->code_system_name, ['ICD-10', 'ICD10'] ) || in_array( $problemCodes->code_system, ['2.16.840.1.113883.6.3', '2.16.840.1.113883.6.4'] ) ) {
                foreach ( $cpmProblems as $cpmProblem ) {
                    if ( (string)$problemCodes->code >= (string)$cpmProblem->icd10from
                        && (string)$problemCodes->code <= (string)$cpmProblem->icd10to
                    ) {
                        array_push( $problemsToActivate, $cpmProblem->name );
                        continue 2;
                    }
                }
            }

            /*
             * Try to match keywords
             */
            foreach ( $cpmProblems as $cpmProblem ) {
                $keywords = explode( ',', $cpmProblem->contains );

                foreach ( $keywords as $keyword ) {
                    if ( empty($keyword) ) continue;

                    if ( strpos( $problemCodes->name, $keyword ) ) {
                        array_push( $problemsToActivate, $cpmProblem->name );
                        continue 3;
                    }
                }
            }
        }
        return $problemsToActivate;
    }
}