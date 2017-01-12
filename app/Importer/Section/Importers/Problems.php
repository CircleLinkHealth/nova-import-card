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
                'ccda_id'                    => $medicalRecordId,
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

            /*
             * ICD-9 Check
             */
            if (in_array($importedProblem->code_system_name, [
                    'ICD-9',
                    'ICD9',
                ]) || $importedProblem->code_system == '2.16.840.1.113883.6.103'
            ) {
                foreach ($cpmProblems as $cpmProblem) {
                    if ($importedProblem->code >= $cpmProblem->icd9from
                        && $importedProblem->code <= $cpmProblem->icd9to
                    ) {
                        array_push($problemsToActivate, $cpmProblem->name);
                        $importedProblem->activate = true;
                        $importedProblem->cpm_problem_id = $cpmProblem->id;
                        $importedProblem->save();
                        continue 2;
                    }
                }
            }

            /*
                 * SNOMED Check
                 */
            if (in_array($importedProblem->code_system_name,
                    ['SNOMED CT']) || $importedProblem->code_system == '2.16.840.1.113883.6.96'
            ) {
                $potentialICD10List = SnomedToCpmIcdMap::whereSnomedCode($importedProblem->code)->pluck('icd_10_code')->all();

                if (!empty($potentialICD10List[0])) {
                    $importedProblem->code_system_name = 'ICD-10';
                    $importedProblem->code_system = '2.16.840.1.113883.6.3';
                    $importedProblem->code = $potentialICD10List[0];
                }
            }

            /*
             * ICD-10 Check
             */
            if (in_array($importedProblem->code_system_name, [
                    'ICD-10',
                    'ICD10',
                    'ICD-10-CM',
                ]) || in_array($importedProblem->code_system, [
                    '2.16.840.1.113883.6.3',
                    '2.16.840.1.113883.6.4',
                ])
            ) {
                foreach ($cpmProblems as $cpmProblem) {
                    if ((string)$importedProblem->code >= (string)$cpmProblem->icd10from
                        && (string)$importedProblem->code <= (string)$cpmProblem->icd10to
                    ) {
                        array_push($problemsToActivate, $cpmProblem->name);
                        $importedProblem->activate = true;
                        $importedProblem->cpm_problem_id = $cpmProblem->id;
                        $importedProblem->save();
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
                    if (empty($keyword)) {
                        continue;
                    }

                    if (strpos($importedProblem->name, $keyword)) {
                        array_push($problemsToActivate, $cpmProblem->name);
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