<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\SharedModels\Entities\Allergy;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;

class ImportAllergies extends BaseCcdaImportTask
{
    /**
     * @param object $allergy
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
                return null;
            }
        
            if (empty($new['allergen_name'])) {
                return null;
            }
            
            if (str_contains(strtolower($new['allergen_name']), 'no known')) {
                return null;
            }
        
            Allergy::updateOrCreate(
                [
                    'patient_id'    => $this->patient->id,
                    'allergen_name' => $new['allergen_name'],
                ]
            );
        });
    
        $this->patient->load('ccdAllergies');
    
        $unique = $this->patient->ccdAllergies->unique('name')->pluck('id')->all();
    
        $deleted = $this->patient->ccdAllergies()->whereNotIn('id', $unique)->delete();
    
        $misc = CpmMisc::whereName(CpmMisc::ALLERGIES)
                       ->first();
    
        if (!empty($unique) && ! $this->hasMisc($this->patient, $misc)) {
            $this->patient->cpmMiscs()->attach(optional($misc)->id);
        }
    }
}