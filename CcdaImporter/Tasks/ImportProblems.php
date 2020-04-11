<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use App\MedicationGroupsMap;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\FiresImportingHooks;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\ConsolidatesProblemInfo;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Entities\Medication;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;

class ImportProblems extends BaseCcdaImportTask
{
    const HOOK_USE_DIFFERENT_INSTRUCTION_IMPORTER  = 'USE_DIFFERENT_INSTRUCTION_IMPORTER';
    
    use ConsolidatesProblemInfo;
    use FiresImportingHooks;
    
    /**
     * @param object $problem
     *
     * @return array
     */
    private function transform(object $problem): array
    {
        return $this->getTransformer()->problem($problem);
    }
    
    protected function import()
    {
        collect($this->ccda->bluebuttonJson()->problems ?? [])->each(
            function ($problem) use (&$medicationGroups) {
                $new = (array) $this->consolidateProblemInfo((object) $this->transform($problem));
            
                if ( ! $this->validate($new)) {
                    return null;
                }
            
                if ( ! $new['cons_name'] && ! $new['cons_text']) {
                    return null;
                }
    
                $instruction = $this->getInstruction($problem);
    
                $ccdProblem = Problem::updateOrCreate(
                    [
                        'name'           => $problem->name,
                        'patient_id'     => $this->patient->id,
                        'cpm_problem_id' => $problem->cpm_problem_id,
                    ],
                    [
                        'problem_import_id'  => $problem->id,
                        'is_monitored'       => (bool)$problem->cpm_problem_id,
                        'ccd_problem_log_id' => $problem->ccd_problem_log_id,
                        'cpm_instruction_id' => optional($instruction)->id ?? null,
                    ]
                );
    
                $problemLog = $problem->ccdLog;
    
                if ($problemLog) {
                    $problemLog->codes->map(
                        function ($codeLog) use ($ccdProblem) {
                            ProblemCode::updateOrCreate(
                                [
                                    'problem_id' => $ccdProblem->id,
                                    'code'       => $codeLog->code,
                                ],
                                [
                                    'code_system_name' => $codeLog->code_system_name,
                                    'code_system_oid'  => $codeLog->code_system_oid,
                                ]
                            );
                        }
                    );
                }
            }
        );
    
        $misc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)
                       ->first();
    
        if ( ! $this->hasMisc($this->patient, $misc)) {
            $this->patient->cpmMiscs()->attach(optional($misc)->id);
        }
        ////////////
        
        $this->patient->load('ccdMedications');
        
        $unique = $this->patient->ccdMedications->unique('name')->pluck('id');
        
        $deleted = $this->patient->ccdMedications()->whereNotIn('id', $unique)->delete();
        
        $this->patient->cpmMedicationGroups()->sync(array_filter($medicationGroups));
        
        $misc = CpmMisc::whereName(CpmMisc::MEDICATION_LIST)
                       ->first();
        
        if ( ! $this->hasMisc($this->patient, $misc)) {
            $this->patient->cpmMiscs()->attach(optional($misc)->id);
        }
    }
    
    private function getInstruction($newProblem)
    {
        $this->fireImportingHook(self::HOOK_USE_DIFFERENT_INSTRUCTION_IMPORTER, $this->patient, $this->ccda, $newProblem);
        
        return (new GetProblemInstruction())->for($newProblem);
    }
    
}