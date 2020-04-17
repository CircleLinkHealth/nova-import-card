<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy;

class ImportInsurances extends BaseCcdaImportTask
{
    /**
     * @param object $insurance
     *
     * @return array
     */
    private function transform(object $insurance): array
    {
        return $this->getTransformer()->insurance($insurance);
    }
    
    protected function import()
    {
        collect($this->ccda->bluebuttonJson()->payers ?? [])->each(
            function ($payer) {
                $new = $this->transform($payer);
                
                if (empty($new['name'])) {
                    return;
                }
                
                $insurance = CcdInsurancePolicy::updateOrCreate(
                    [
                        'patient_id' => $this->patient->id,
                        'name' => $new['name'],
                    ],
                    array_merge(
                        [
                            'medical_record_id'   => $this->ccda->id,
                            'medical_record_type' => get_class($this->ccda),
                        ],
                        $new
                    )
                );
                
                if ($insurance->wasRecentlyCreated) {
                    $insurance->approved = false;
                    $insurance->save();
                }
            }
        );
    }
}