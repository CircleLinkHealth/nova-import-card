<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 5:52 PM
 */

namespace App\Services\CPM;


use App\Models\CPM\CpmMisc;
use App\Models\UI\Section;
use App\PatientCarePlan as CarePlan;
use App\CarePlanTemplate;
use App\User;

/**
 * This Class does the needful to get the data needed for CarePlan Views and feed it to them.
 *
 * Class CarePlanViewService
 * @package App\Services\CPM
 */
class CarePlanViewService
{
    public function carePlanFirstPage(CarePlan $carePlan)
    {
        if (empty($carePlan)) return false;

        $cptId = $carePlan->care_plan_template_id;
        $template = CarePlanTemplate::find($cptId);

        //This cannot be null because we have a foreign key constraint.
        //If a CarePlanTemplate is deleted all it's CarePlans are also deleted
        //ie. We want an exception to be thrown so keep this commented out, unless it's an emergency.
        //
        // if (empty($template)) abort(404, 'Care Plan Template not found.');

        //get the User's cpmProblems
        $patient = User::find($carePlan->patient_id);
        $patientProblems = $patient->cpmProblems()->get()->lists('id');

        $template = $template->loadWithInstructionsAndSort([
            'cpmLifestyles',
            'cpmMedicationGroups',
            'cpmProblems',
        ]);

        $problems = new Section();
        $problems->name = 'problems';
        $problems->title = 'Diagnosis / Problems to Monitor';
        $problems->items = $template->cpmProblems;
        $problems->patientItemIds = $patientProblems;
        $problems->miscs = $template->cpmMiscs()->where('name', CpmMisc::OTHER_CONDITIONS)->get();

        $lifestyles = new Section();
        $lifestyles->name = 'lifestyles';
        $lifestyles->title = 'Lifestyle to Monitor';
        $lifestyles->items = $template->cpmLifestyles;

        $medications = new Section();
        $medications->name = 'medications';
        $medications->title = 'Medications to Monitor';
        $medications->items = $template->cpmMedicationGroups;
        $medications->miscs = $template->cpmMiscs()->where('name', CpmMisc::MEDICATION_LIST)->get();

        $sections = [
            $problems,
            $lifestyles,
            $medications,
        ];

        return compact('sections');

    }

    public function carePlanSecondPage(CarePlan $carePlan)
    {
        if (empty($carePlan)) return false;

        $cptId = $carePlan->care_plan_template_id;
        $template = CarePlanTemplate::find($cptId);

        $template = $template->loadWithInstructionsAndSort([
            'cpmBiometrics',
        ]);

        $transCare = new Section();
        $transCare->name = 'trans-care';
        $transCare->title = 'Transitional Care Management';
        $transCare->miscs = $template->cpmMiscs()->whereIn('name', [
            CpmMisc::TRACK_CARE_TRANSITIONS,
        ])->orderBy('pivot_ui_sort')->get();

        $biometrics = new Section();
        $biometrics->name = 'biometrics';
        $biometrics->title = 'Transitional Care Management';
        $biometrics->items = $template->cpmBiometrics;

        //Add sections here in order
        $sections = [
            $biometrics,
            $transCare
        ];

        return compact('sections');
    }


    public function carePlanThirdPage(CarePlan $carePlan)
    {
        if (empty($carePlan)) return false;

        $cptId = $carePlan->care_plan_template_id;
        $template = CarePlanTemplate::find($cptId);

        $template = $template->loadWithInstructionsAndSort([
                'cpmSymptoms',
            ]);

        $symptoms = new Section();
        $symptoms->name = 'symptoms';
        $symptoms->title = 'Symptoms to Monitor';
        $symptoms->items = $template->cpmSymptoms;

        $additionalInfo = new Section();
        $additionalInfo->name = 'additional-infos';
        $additionalInfo->title = 'Additional Information';
        $additionalInfo->miscs = $template->cpmMiscs()->whereIn('name', [
                CpmMisc::ALLERGIES,
                CpmMisc::APPOINTMENTS,
                CpmMisc::SOCIAL_SERVICES,
                CpmMisc::OTHER,
            ])
            ->orderBy('pivot_ui_sort')->get();

        //Add sections here in order
        $sections = [
            $symptoms,
            $additionalInfo
        ];

        return compact('sections');
    }

}