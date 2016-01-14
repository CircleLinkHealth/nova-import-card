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
            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $problem;

            if (empty($problemCodes->code)) {
                $problemCodes = $problem->translation;
            }

            /*
             * ICD-9 Check
             */
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

                return;
            }

            /*
             * ICD-10 Check
             */
            if ($problemCodes->code_system_name == 'ICD-10' || $problemCodes->code_system == '2.16.840.1.113883.6.3')
            {
                foreach ($ccdProblems as $ccdProblem)
                {
                    if ((string) $problemCodes->code >= (string) $ccdProblem->icd10from
                        && (string) $problemCodes->code <= (string) $ccdProblem->icd10to)
                    {
                        //activate condition in care plan
                    }
                }

                return;
            }

            /*
             * Try to match keywords
             */
            foreach ($ccdProblems as $ccdProblem)
            {
                $keywords = explode(',', $ccdProblem->contains);

                foreach ($keywords as $keyword)
                {
                    if (strpos($ccdProblem->name, $keyword))
                    {
                        //activate condition in care plan
                    }
                }
            }
        }
    }
}