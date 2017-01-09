<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Problems;

use App\CLH\CCD\ImportedItems\ProblemImport;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Models\CCD\Ccda;

class NameCodeCodeSysNameList implements ParsingStrategy
{
    use ConsolidatesProblemInfo;

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $problemsSection = ProblemLog::whereCcdaId($ccd->id)->get();;

        $problemsList = '';

        foreach ( $problemsSection as $ccdProblem ) {
            if ( !$validator->validate( $ccdProblem ) ) continue;

            $ccdProblem->import = true;
            $ccdProblem->save();

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $this->consolidateProblemInfo( $ccdProblem );

            $problemsList[] = (new ProblemImport())->updateOrCreate([
                'ccda_id' => $ccd->id,
                'vendor_id' => $ccd->vendor_id,
                'ccd_problem_log_id' => $ccdProblem->id,
                'name' => $problemCodes->cons_name,
                'code' => $problemCodes->cons_code,
                'code_system' => $problemCodes->cons_code_system,
                'code_system_name' => $problemCodes->cons_code_system_name,
            ]);
        }

        return $problemsList;
    }
}