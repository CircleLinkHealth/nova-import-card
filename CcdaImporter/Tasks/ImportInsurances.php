<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaSectionImporter;
use CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy;

class ImportInsurances extends BaseCcdaSectionImporter
{
    /**
     * @param object $insurance
     *
     * @return array
     */
    private function transform(object $insurance):array
    {
        return $this->getTransformer()->insurance($insurance);
    }
    
    protected function import()
    {
        collect($this->ccda->bluebuttonJson()->payers ?? [])->each(function ($payer) {
            $new = $this->transform($payer);
    
            if (empty($new['name'])) {
                return;
            }
    
            $insurance = CcdInsurancePolicy::updateOrCreate(
                array_merge([
                                'patient_id' => $this->patient->id,
                                'medical_record_id' => $this->ccda->id,
                                'medical_record_type' => get_class($this->ccda),
                            ],
                            $new)
            );
        });
    }
}