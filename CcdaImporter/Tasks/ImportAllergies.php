<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\SharedModels\Entities\Allergy;
use CircleLinkHealth\SharedModels\Entities\AllergyLog;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;

class ImportAllergies extends BaseCcdaImportTask
{
    /**
     * @param AllergyLog $allergy
     *
     * @return array
     */
    private function transform(object $allergy):array
    {
        return $this->getTransformer()->allergy($allergy);
    }
    
    protected function import()
    {
        collect($this->ccda->bluebuttonJson()->allergies ?? [])->each(function ($allergy) {
            $new = $this->transform($allergy);
        
            if ( ! $this->validate($new)) {
                null;
            }
        
            if (empty($new['allergen_name'])) {
                null;
            }
        
            Allergy::updateOrCreate(
                [
                    'allergen_name' => $new['allergen_name'],
                ],
                [
                    'patient_id'         => $this->patient->id,
                ]
            );
        });
    
        $this->patient->load('ccdAllergies');
    
        $unique = $this->patient->ccdAllergies->unique('name')->pluck('id');
    
        $deleted = $this->patient->ccdAllergies()->whereNotIn('id', $unique)->delete();
    
        $misc = CpmMisc::whereName(CpmMisc::ALLERGIES)
                       ->first();
    
        if ( ! $this->hasMisc($this->patient, $misc)) {
            $this->patient->cpmMiscs()->attach(optional($misc)->id);
        }
    }
}