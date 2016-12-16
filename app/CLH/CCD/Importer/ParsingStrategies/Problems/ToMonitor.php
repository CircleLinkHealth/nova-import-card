<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Problems;


use App\CLH\CCD\ImportedItems\ProblemImport;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\Models\CCD\Ccda;
use App\Models\CPM\CpmProblem;

class ToMonitor implements ParsingStrategy
{
    use ConsolidatesProblemInfo;

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $problemsSection = ProblemImport::whereCcdaId($ccd->id)->get();

        $cpmProblems = CpmProblem::all();

        $problemsToActivate = [];

        foreach ($problemsSection as $ccdProblem) {

//            if ( empty($ccdProblem->code_system_name) && empty($ccdProblem->code_system) ) continue;

            /*
             * ICD-9 Check
             */
            if (in_array($ccdProblem->code_system_name, ['ICD-9', 'ICD9']) || $ccdProblem->code_system == '2.16.840.1.113883.6.103') {
                foreach ($cpmProblems as $cpmProblem) {
                    if ($ccdProblem->code >= $cpmProblem->icd9from
                        && $ccdProblem->code <= $cpmProblem->icd9to
                    ) {
                        array_push($problemsToActivate, $cpmProblem->name);
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
            if (in_array($ccdProblem->code_system_name, ['SNOMED CT']) || $ccdProblem->code_system == '2.16.840.1.113883.6.96') {
                $potentialICD10List = SnomedToCpmIcdMap::whereSnomedCode($ccdProblem->code)->pluck('icd_10_code')->all();

                if (!empty($potentialICD10List[0])) {
                    $ccdProblem->code_system_name = 'ICD-10';
                    $ccdProblem->code_system = '2.16.840.1.113883.6.3';
                    $ccdProblem->code = $potentialICD10List[0];
                }
            }

            /*
             * ICD-10 Check
             */
            if (in_array($ccdProblem->code_system_name, ['ICD-10', 'ICD10', 'ICD-10-CM']) || in_array($ccdProblem->code_system, ['2.16.840.1.113883.6.3', '2.16.840.1.113883.6.4'])) {
                foreach ($cpmProblems as $cpmProblem) {
                    if ((string)$ccdProblem->code >= (string)$cpmProblem->icd10from
                        && (string)$ccdProblem->code <= (string)$cpmProblem->icd10to
                    ) {
                        array_push($problemsToActivate, $cpmProblem->name);
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
            foreach ($cpmProblems as $cpmProblem) {
                $keywords = explode(',', $cpmProblem->contains);

                foreach ($keywords as $keyword) {
                    if (empty($keyword)) continue;

                    if (strpos($ccdProblem->name, $keyword)) {
                        array_push($problemsToActivate, $cpmProblem->name);
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