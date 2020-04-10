<?php

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaSectionImporter;
use CircleLinkHealth\SharedModels\Entities\CarePlan;

class FirstOrCreateCarePlan extends BaseCcdaSectionImporter
{
    protected function import()
    {
        $args = [
            'care_plan_template_id' => $this->patient->service()->firstOrDefaultCarePlan(
                $this->patient
            )->care_plan_template_id,
            'status'                => 'draft',
        ];
    
        $carePlan = CarePlan::firstOrCreate(
            [
                'user_id' => $this->patient->id,
            ],
            $args
        );
    
        if ( ! $carePlan->care_plan_template_id) {
            $carePlan->care_plan_template_id = $args['care_plan_template_id'];
        }
    
        if ( ! $carePlan->status) {
            $carePlan->status = $args['status'];
        }
    
        if ($carePlan->isDirty()) {
            $carePlan->save();
        }
    
        return $carePlan;
    }
}