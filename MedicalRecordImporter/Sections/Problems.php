<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections;

use App\Constants;
use App\Importer\Section\Validators\NameNotNull;
use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;

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
                                    ->get();
        
        $shouldValidate = true;
        
        $haveName = $problemsGroups->reject(
            function (ProblemLog $p) {
                return ! (new NameNotNull())->isValid($p);
            }
        )->count();
        
        $haveCode = $problemsGroups->reject(
            function (ProblemLog $p) {
                return empty($this->consolidateProblemInfo($p)->cons_code);
            }
        )->filter()->count();
        
        if ($haveCode > $haveName) {
            $shouldValidate = false;
        }
        
        if ($shouldValidate) {
            $problemsGroups = $problemsGroups->unique(
                function ($itemLog) {
                    $name = $itemLog->name ?? $itemLog->reference_title ?? $itemLog->translation_name;
                    
                    return empty($name)
                        ? false
                        : $name;
                }
            )
                           ->values();
        }
    
        $problemsGroups = $problemsGroups->mapToGroups(
            function ($itemLog) use (
                $medicalRecordType,
                $medicalRecordId,
                $importedMedicalRecord,
                $shouldValidate
            ) {
                if ($shouldValidate && ! $this->validate($itemLog)) {
                    return ['do_not_import' => $itemLog->id];
                }
                
                /**
                 * Check if the information is in the Translation Section of BB.
                 */
                $problemCodes = $this->consolidateProblemInfo($itemLog);
                
                if ( ! validProblemName($problemCodes->cons_name)) {
                    return ['do_not_import' => $itemLog->id];
                }
                
                $cpmProblem   = $this->getCpmProblem($itemLog, $problemCodes->cons_name);
                $cpmProblemId = optional($cpmProblem)->id;
                
                //if problem is Diabetes and string contains 2, it's probably diabetes type 2
                if (1 == $cpmProblemId && str_contains($problemCodes->cons_name, ['2'])) {
                    $cpmProblem = $this->cpmProblems->firstWhere(
                        'name',
                        'Diabetes Type 2'
                    );
                } //if problem is Diabetes and string contains 1, it's probably diabetes type 1
                elseif (1 == $cpmProblemId && str_contains(
                        $problemCodes->cons_name,
                        ['1']
                    )) {
                    $cpmProblem = $this->cpmProblems->firstWhere(
                        'name',
                        'Diabetes Type 1'
                    );
                }
                
                $problem = [
                    'attributes'    => [
                        'medical_record_type'        => $medicalRecordType,
                        'medical_record_id'          => $medicalRecordId,
                        'imported_medical_record_id' => $importedMedicalRecord->id,
                        'ccd_problem_log_id'         => $itemLog->id,
                        'name'                       => $problemCodes->cons_name,
                        'cpm_problem_id'             => $cpmProblemId,
                    ],
                    'is_behavioral' => optional($cpmProblem)->is_behavioral,
                    'itemLog'       => $itemLog,
                ];
                
                if ($cpmProblem) {
                    return ['monitored' => $problem];
                }
                
                return ['not_monitored' => $problem];
            }
        );
        
        $callback = function ($monitored) {
            $monitored['itemLog']->import = true;
            $monitored['itemLog']->save();
            
            return ProblemImport::updateOrCreate($monitored['attributes']);
        };
        
        $monitored = $problemsGroups->get('monitored', collect());
        
        if ($shouldValidate) {
            $monitored = $monitored->unique(
                function ($p) {
                    return $p['attributes']['name'];
                }
            );
        }
        
        $monitored = $monitored->map($callback);
        
        $notMonitored = $problemsGroups->get('not_monitored', collect());
        
        if ($shouldValidate) {
            $notMonitored = $notMonitored->unique(
                function ($p) {
                    return $p['attributes']['name'];
                }
            );
        }
        
        $notMonitored = $notMonitored->map($callback);
        
        return $problemsGroups;
    }
    
    /**
     * Get the CpmProblem for a ProblemLog.
     *
     * @param ProblemLog $itemLog
     * @param $problemName
     *
     * @return CpmProblem|null
     */
    private function getCpmProblem(ProblemLog $itemLog, $problemName)
    {
        if ( ! validProblemName($problemName)) {
            return null;
        }
        
        $codes = $itemLog
            ->codes()
            ->pluck('code')
            ->all();
        
        $problemMap = SnomedToCpmIcdMap::with('cpmProblem')
                                       ->has('cpmProblem')
                                       ->where(
                                           function ($q) use ($codes) {
                                               $q->whereIn(Constants::ICD9, $codes)
                                                 ->where(Constants::ICD9, '!=', '')
                                                 ->whereNotNull(Constants::ICD9);
                                           }
                                       )
                                       ->orWhere(
                                           function ($q) use ($codes) {
                                               $q->whereIn(Constants::ICD10, $codes)
                                                 ->where(Constants::ICD10, '!=', '')
                                                 ->whereNotNull(Constants::ICD10);
                                           }
                                       )
                                       ->orWhere(
                                           function ($q) use ($codes) {
                                               $q->whereIn(Constants::SNOMED, $codes)
                                                 ->where(Constants::SNOMED, '!=', '')
                                                 ->whereNotNull(Constants::SNOMED);
                                           }
                                       )
                                       ->first();
        
        if ($problemMap) {
            return $problemMap->cpmProblem;
        }
        
        // Try to match keywords
        foreach ($this->cpmProblems as $cpmProblem) {
            //Do not perform keyword matching if name is just Cancer
            //https://circlelinkhealth.atlassian.net/browse/CPM-108
            if (0 === strcasecmp($problemName, 'cancer')) {
                break;
            }
            
            $keywords = array_filter(array_merge(explode(',', $cpmProblem->contains), [$cpmProblem->name]));
            
            foreach ($keywords as $keyword) {
                if ( ! $keyword || empty($keyword)) {
                    continue;
                }
                
                $keyword = trim($keyword);
                
                if (str_contains(strtolower($problemName), strtolower($keyword))) {
                    return $cpmProblem;
                }
            }
        }
    }
}
