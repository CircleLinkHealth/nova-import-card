<?php

namespace App\Observers;

use App\CarePlan;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmMisc;

class CarePlanObserver
{
    /**
     * Listen to the CarePlan saving event.
     *
     * @param CarePlan $carePlan
     *
     */
    public function saving(CarePlan $carePlan)
    {
        if ($carePlan->status == CarePlan::QA_APPROVED) {
            $carePlan->provider_approver_id = null;
        }

        if (!array_key_exists('care_plan_template_id', $carePlan->getAttributes())) {
            $carePlan->care_plan_template_id = getDefaultCarePlanTemplate()->id;
        }

        if ($carePlan->patient->practice('upg')) {
            $cpmMisc = CpmMisc::whereName('Other')->first();

            $instruction = CpmInstruction::updateOrCreate([
                'is_default' => true,
                'name'       => '- Take all of your medications as prescribed.

- Exercise your heart and muscles regularly.

- Maintain a healthy weight.

- Eat heart-healthy foods and avoid overeating.

- Please notify your care team if you are in the hospital by calling (844) 968-1800.

- Get a flu shot every year but check with your provider first. Ask your provider if you should get a pneumococcal (pneumonia) vaccine, tetanus (Tdap) vaccine or a zoster (shingles) vaccine.',
            ]);

            $patientMiscExists = $carePlan->patient->cpmMiscs()->where('cpm_misc_id', $cpmMisc->id)->first();

            if ($patientMiscExists) {
                $carePlan->patient->cpmMiscs()->detach($patientMiscExists);
            }

            $patientMisc = $carePlan->patient->cpmMiscs()->attach($cpmMisc->id, [
                'cpm_instruction_id' => $instruction->id,
            ]);
        }
    }
}
