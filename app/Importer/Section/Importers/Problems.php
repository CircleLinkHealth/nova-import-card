<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
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

    private $cpmProblems;

    public function __construct()
    {
        $this->cpmProblems = CpmProblem::all();
    }

    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $problemsGroups = ProblemLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->get()
            ->unique(function ($itemLog) {
                $name = $itemLog->name ?? $itemLog->reference_title;

                return empty($name)
                                            ? $itemLog->translation_name
                                            : $name;
            })
            ->values()
            ->mapToGroups(function ($itemLog) use (
                                        $medicalRecordType,
                                        $medicalRecordId,
                                        $importedMedicalRecord
                                    ) {
                if (!$this->validate($itemLog)) {
                    return ['do_not_import' => $itemLog->id];
                }

                /**
                 * Check if the information is in the Translation Section of BB.
                 */
                $problemCodes = $this->consolidateProblemInfo($itemLog);

                if (!validProblemName($problemCodes->cons_name)) {
                    return ['do_not_import' => $itemLog->id];
                }

                $cpmProblemId = $this->getCpmProblemId($itemLog, $problemCodes->cons_name);

                if (1 == $cpmProblemId && str_contains($problemCodes->cons_name, ['2'])) {
                    $cpmProblemId = $this->cpmProblems->firstWhere(
                                                'name',
                                                'Diabetes Type 2'
                                            )->id;
                } elseif (1 == $cpmProblemId && str_contains(
                                            $problemCodes->cons_name,
                                                ['1']
                                        )) {
                    $cpmProblemId = $this->cpmProblems->firstWhere(
                                                'name',
                                                'Diabetes Type 1'
                                            )->id;
                } elseif (1 == $cpmProblemId) {
                    return ['do_not_import' => $itemLog->id];
                }

                $problem = [
                    'attributes' => [
                        'medical_record_type'        => $medicalRecordType,
                        'medical_record_id'          => $medicalRecordId,
                        'imported_medical_record_id' => $importedMedicalRecord->id,
                        'ccd_problem_log_id'         => $itemLog->id,
                        'name'                       => $problemCodes->cons_name,
                        'cpm_problem_id'             => $cpmProblemId,
                    ],
                    'itemLog' => $itemLog,
                ];

                if ($cpmProblemId) {
                    return ['monitored' => $problem];
                }

                //do not import not monitored conditions for ottawa
                if (158 == $importedMedicalRecord->practice_id) {
                    return ['do_not_import' => $problem];
                }

                return ['not_monitored' => $problem];
            });

        $callback = function ($monitored) {
            $monitored['itemLog']->import = true;
            $monitored['itemLog']->save();

            return ProblemImport::updateOrCreate($monitored['attributes']);
        };

        $monitored = $problemsGroups->get('monitored', collect())
            ->unique(function ($p) {
                return $p['attributes']['cpm_problem_id'];
            })
            ->map($callback);

        $notMonitored = $problemsGroups->get('not_monitored', collect())
            ->unique(function ($p) {
                return $p['attributes']['name'];
            })
            ->map($callback);

        return $problemsGroups;
    }

    private function getCpmProblemId(ProblemLog $itemLog, $problemName)
    {
        if (!validProblemName($problemName)) {
            return null;
        }

        $map = $itemLog
            ->codeMap();

        foreach ($map as $codeSystemName => $code) {
            $problemMap = SnomedToCpmIcdMap::where($codeSystemName, '=', $code)
                ->first();

            if ($problemMap) {
                return $problemMap->cpm_problem_id;
            }
        }

        // Try to match keywords
        foreach ($this->cpmProblems as $cpmProblem) {
            //Do not perform keyword matching if name is just Cancer
            //https://circlelinkhealth.atlassian.net/browse/CPM-108
            if (0 === strcasecmp($problemName, 'cancer')) {
                break;
            }

            $keywords = array_merge(explode(',', $cpmProblem->contains), [$cpmProblem->name]);

            foreach ($keywords as $keyword) {
                if (!$keyword || empty($keyword)) {
                    continue;
                }

                $keyword = trim($keyword);

                if (str_contains(strtolower($problemName), strtolower($keyword)) || str_contains(
                    strtolower($keyword),
                        strtolower($problemName)
                )) {
                    return $cpmProblem->id;
                }
            }
        }
    }
}
