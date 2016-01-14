<?php

namespace App\CLH\CCD\Importer\Parsers;


use App\CLH\CCD\Importer\CCDProblem;

class ProblemsParser extends BaseParser
{

    public function parse()
    {
        $ccdProblems = CCDProblem::all();
        $problems = $this->ccd->problems;

        foreach ($problems as $problem)
        {
            //ICD 9 check
            $problemCodes = $problem;
            if (empty($problemCodes->code)) {
                $problemCodes = $problem->translation;
            }

            if ($problemCodes->code_system_name == 'ICD-9' || $problemCodes->code_system == '2.16.840.1.113883.6.103')
            {
                foreach ($ccdProblems as $ccdProblem)
                {
                    if ($problemCodes->code >= $ccdProblem->icd9from
                        && $problemCodes->code <= $ccdProblem->icd9to)
                    {
                        //activate condition in care plan
                    }
                }
            }
        }
    }
}