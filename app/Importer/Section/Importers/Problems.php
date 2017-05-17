<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/01/2017
 * Time: 11:39 PM
 */

namespace App\Importer\Section\Importers;


use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord;
use App\Importer\Models\ImportedItems\ProblemImport;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Models\CPM\CpmProblem;

class Problems extends BaseImporter
{
    use ConsolidatesProblemInfo;

    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $itemLogs = ProblemLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->get();

        $problemsList = [];

        foreach ($itemLogs as $itemLog) {
            if (!$this->validate($itemLog)) {
                continue;
            }

            $itemLog->import = true;
            $itemLog->save();

            /**
             * Check if the information is in the Translation Section of BB
             */
            $problemCodes = $this->consolidateProblemInfo($itemLog);

            $problemsList[] = ProblemImport::updateOrCreate([
                'medical_record_type'        => $medicalRecordType,
                'medical_record_id'          => $medicalRecordId,
                'imported_medical_record_id' => $importedMedicalRecord->id,
                'vendor_id'                  => $itemLog->vendor_id,
                'ccd_problem_log_id'         => $itemLog->id,
                'name'                       => $problemCodes->cons_name,
                'code'                       => $problemCodes->cons_code,
                'code_system'                => $problemCodes->cons_code_system,
                'code_system_name'           => $problemCodes->cons_code_system_name,
            ]);
        }

        $this->activateBillableProblems($problemsList);
    }

    /*
    * Figure out which CPMProblems to activate on the CarePlan
    */
    public function activateBillableProblems(array $problemImports)
    {
        $cpmProblems = CpmProblem::all();

        $problemsToActivate = [];

        foreach ($problemImports as $importedProblem) {

            if (!$importedProblem->code) {
                continue;
            }

            $codeType = $importedProblem->getCodeType();

            if ($codeType && $importedProblem->code) {
                $problemMap = SnomedToCpmIcdMap::where($codeType, '=', $importedProblem->code)
                    ->first();

                if ($problemMap) {
                    array_push($problemsToActivate, $problemMap->cpm_problem_id);
                    $importedProblem->activate = true;
                    $importedProblem->cpm_problem_id = $problemMap->cpm_problem_id;
                    $importedProblem->save();
                    continue;
                }
            }

            //try icd 9
            $problemMap = SnomedToCpmIcdMap::where('icd_9_code', '=', $importedProblem->code)
                ->first();

            if ($problemMap) {
                array_push($problemsToActivate, $problemMap->cpm_problem_id);
                $importedProblem->code_system = '2.16.840.1.113883.6.103';
                $importedProblem->code_system_name = 'ICD-9-CM';
                $importedProblem->activate = true;
                $importedProblem->cpm_problem_id = $problemMap->cpm_problem_id;
                $importedProblem->save();
                continue;
            }

            //try icd 10
            $problemMap = SnomedToCpmIcdMap::where('icd_10_code', '=', $importedProblem->code)
                ->first();

            if ($problemMap) {
                array_push($problemsToActivate, $problemMap->cpm_problem_id);
                $importedProblem->code_system = '2.16.840.1.113883.6.3';
                $importedProblem->code_system_name = 'ICD-10-CM';
                $importedProblem->activate = true;
                $importedProblem->cpm_problem_id = $problemMap->cpm_problem_id;
                $importedProblem->save();
                continue;
            }

            /*
             * Try to match keywords
             */
            foreach ($cpmProblems as $cpmProblem) {
                $keywords = array_merge(explode(',', $cpmProblem->contains), [$cpmProblem->name]);

                foreach ($keywords as $keyword) {
                    if (empty($keyword)) {
                        continue;
                    }

                    if (str_contains(strtolower($importedProblem->name), strtolower($keyword))) {
                        array_push($problemsToActivate, $cpmProblem->id);
                        $importedProblem->activate = true;
                        $importedProblem->cpm_problem_id = $cpmProblem->id;
                        $importedProblem->save();
                        continue 3;
                    }
                }
            }
        }

        return $problemsToActivate;
    }
}