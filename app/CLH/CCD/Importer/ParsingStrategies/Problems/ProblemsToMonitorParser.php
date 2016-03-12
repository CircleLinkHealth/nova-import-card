<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Problems;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItems\ProblemImport;
use App\CLH\CCD\Importer\CPMProblem;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\CLH\CCD\Importer\SnomedToICD10Map;
use App\CLH\CCD\ItemLogger\CcdProblemLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;

class ProblemsToMonitorParser implements ParsingStrategy
{
    use ConsolidatesProblemInfoTrait;

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $problemsSection = ProblemImport::whereCcdaId($ccd->id)->get();

        $cpmProblems = CPMProblem::all();

        $problemsToActivate = [];

        foreach ( $problemsSection as $ccdProblem ) {

            if ( !$validator->validate( $ccdProblem ) ) continue;

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $this->consolidateProblemInfo( $ccdProblem );

            if ( empty($problemCodes->cons_code_system_name) && empty($problemCodes->cons_code_system) ) continue;

            /*
             * ICD-9 Check
             */
            if ( in_array( $problemCodes->cons_code_system_name, ['ICD-9', 'ICD9'] ) || $problemCodes->cons_code_system == '2.16.840.1.113883.6.103' ) {
                foreach ( $cpmProblems as $cpmProblem ) {
                    if ( $problemCodes->cons_code >= $cpmProblem->icd9from
                        && $problemCodes->cons_code <= $cpmProblem->icd9to
                    ) {
                        array_push( $problemsToActivate, $cpmProblem->name );
                        $ccdProblem->activate = true;
                        $ccdProblem->cpm_problem_id = $cpmProblem->id;
                        $ccdProblem->save();
                        continue 2;
                    }
                }
            }

            /*
                 * SNOMED Check
                 */
            if ( in_array( $problemCodes->cons_code_system_name, ['SNOMED CT'] ) || $problemCodes->cons_code_system == '2.16.840.1.113883.6.96' ) {
                $potentialICD10List = SnomedToCpmIcdMap::whereSnomedCode( $problemCodes->cons_code )->lists( 'icd_10_code' );

                if ( !empty($potentialICD10List[ 0 ]) ) {
                    $problemCodes->cons_code_system_name = 'ICD-10';
                    $problemCodes->cons_code_system = '2.16.840.1.113883.6.3';
                    $problemCodes->cons_code = $potentialICD10List[ 0 ];
                }
            }

            /*
             * ICD-10 Check
             */
            if ( in_array( $problemCodes->cons_code_system_name, ['ICD-10', 'ICD10'] ) || in_array( $problemCodes->cons_code_system, ['2.16.840.1.113883.6.3', '2.16.840.1.113883.6.4'] ) ) {
                foreach ( $cpmProblems as $cpmProblem ) {
                    if ( (string)$problemCodes->cons_code >= (string)$cpmProblem->icd10from
                        && (string)$problemCodes->cons_code <= (string)$cpmProblem->icd10to
                    ) {
                        array_push( $problemsToActivate, $cpmProblem->name );
                        $ccdProblem->activate = true;
                        $ccdProblem->cpm_problem_id = $cpmProblem->id;
                        $ccdProblem->save();
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

                    if ( strpos( $problemCodes->cons_name, $keyword ) ) {
                        array_push( $problemsToActivate, $cpmProblem->name );
                        $ccdProblem->activate = true;
                        $ccdProblem->cpm_problem_id = $cpmProblem->id;
                        $ccdProblem->save();
                        continue 3;
                    }
                }
            }
        }
        return $problemsToActivate;
    }
}