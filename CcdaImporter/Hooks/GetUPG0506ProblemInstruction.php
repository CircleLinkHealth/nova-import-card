<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Hooks;


use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportHook;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;

class GetUPG0506ProblemInstruction extends BaseCcdaImportHook
{
    /**
     * @var boolean|null
     */
    protected $hasUPG0506Instructions;
    
    public function for(Ccda $ccda, $newProblem) {
        if (is_null($this->hasUPG0506Instructions)) {
            $this->hasUPG0506Instructions = $this->ccda->hasUPG0506PdfCareplanMedia()->exists();
        }
    
        if (true === $this->hasUPG0506Instructions) {
            return $this->createInstructionFromUPG0506($ccda, $newProblem);
        }
    }
    
    private function createInstructionFromUPG0506(Ccda $ccda, $newProblem): ?CpmInstruction
    {
        $pdfMedia = $ccda->getUPG0506PdfCareplanMedia();
        
        if ( ! $pdfMedia) {
            return null;
        }
        
        $customProperties = json_decode($pdfMedia->custom_properties);
        
        if ( ! isset($customProperties->care_plan)) {
            return null;
        }
        
        $matchingProblem = collect($customProperties->care_plan->instructions)
            ->where('name', $newProblem->name)
            ->first();
        
        
        if ( ! $matchingProblem) {
            return null;
        }
        
        return CpmInstruction::create(
            [
                'name' => $matchingProblem->instructions,
            ]
        );
    }
}