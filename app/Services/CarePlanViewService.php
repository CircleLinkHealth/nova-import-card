<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 5:52 PM
 */

namespace App\Services;


use App\Models\CPM\CpmMisc;
use App\Models\CPM\UI\Biometrics;
use App\Models\CPM\UI\Section;
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
    public function carePlanFirstPage(CarePlan $carePlan, User $patient)
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
        $patientProblems = $patient->cpmProblems()->get();
        $patientProblemsIds = $patientProblems->lists('id')->all();

        $patientLifestyles = $patient->cpmLifestyles()->get();
        $patientLifestylesIds = $patientLifestyles->lists('id')->all();

        $patientMedicationGroups = $patient->cpmMedicationGroups()->get();
        $patientMedicationGroupsIds = $patientMedicationGroups->lists('id')->all();
        
        $patientMiscs = $patient->cpmMiscs()->get();
        $patientMiscsIds = $patientMiscs->lists('id')->all();

        $template = $template->loadWithInstructionsAndSort([
            'cpmLifestyles',
            'cpmMedicationGroups',
            'cpmProblems',
        ]);

        $problems = new Section();
        $problems->name = 'cpmProblems';
        $problems->title = 'Diagnosis / Problems to Monitor';
        $problems->items = $template->cpmProblems;
        $problems->patientItemIds = $patientProblemsIds;
        $problems->patientItems = $patientProblems->keyBy('id');
        $problems->miscs = $template->cpmMiscs()->where('name', CpmMisc::OTHER_CONDITIONS)->get();
        $problems->patientMiscsIds = $patientMiscsIds;
        $problems->patientMiscs = $patientMiscs->keyBy('id');

        $lifestyles = new Section();
        $lifestyles->name = 'cpmLifestyles';
        $lifestyles->title = 'Lifestyle to Monitor';
        $lifestyles->items = $template->cpmLifestyles;
        $lifestyles->patientItemIds = $patientLifestylesIds;
        $lifestyles->patientItems = $patientLifestyles->keyBy('id');

        $medications = new Section();
        $medications->name = 'cpmMedicationGroups';
        $medications->title = 'Medications to Monitor';
        $medications->items = $template->cpmMedicationGroups;
        $medications->patientItemIds = $patientMedicationGroupsIds;
        $medications->patientItems = $patientMedicationGroups->keyBy('id');
        $medications->miscs = $template->cpmMiscs()->where('name', CpmMisc::MEDICATION_LIST)->get();
        $medications->patientMiscsIds = $patientMiscsIds;
        $medications->patientMiscs = $patientMiscs->keyBy('id');

        $sections = [
            $problems,
            $lifestyles,
            $medications,
        ];

        return compact('sections');

    }

    public function carePlanSecondPage(CarePlan $carePlan, User $patient)
    {
        if (empty($carePlan)) return false;

        $cptId = $carePlan->care_plan_template_id;
        $template = CarePlanTemplate::find($cptId);

        $template = $template->loadWithInstructionsAndSort([
            'cpmBiometrics',
        ]);


        $patientMiscs = $patient->cpmMiscs()->get();
        $patientMiscsIds = $patientMiscs->lists('id')->all();

        $transCare = new Section();
        $transCare->name = 'cpmMiscs';
        $transCare->title = 'Transitional Care Management';
        $transCare->miscs = $template->cpmMiscs()->whereIn('name', [
            CpmMisc::TRACK_CARE_TRANSITIONS,
        ])->orderBy('pivot_ui_sort')->get();
        $transCare->patientMiscsIds = $patientMiscsIds;
        $transCare->patientMiscs = $patientMiscs->keyBy('id');


        $bloodPressure = $patient->cpmBloodPressure()->firstOrNew([]);
        $bloodSugar = $patient->cpmBloodSugar()->firstOrNew([]);
        $smoking = $patient->cpmSmoking()->firstOrNew([]);
        $weight = $patient->cpmWeight()->firstOrNew([]);
        
        $patientBiometrics = $patient->cpmBiometrics()->get();

        $biometrics = new Section();
        $biometrics->name = 'cpmBiometrics';
        $biometrics->title = 'Biometrics to Monitor';
        $biometrics->items = $template->cpmBiometrics;
        $biometrics->patientItemIds = $patientBiometrics->lists('id')->all();
        $biometrics->patientItems = $patientBiometrics->keyBy('id');


        //Add sections here in order
        $sections = [
            $biometrics,
            $transCare,
        ];
        
        $biometrics = new Biometrics();
        $biometrics->bloodPressure = $bloodPressure;
        $biometrics->bloodSugar = $bloodSugar;
        $biometrics->smoking = $smoking;
        $biometrics->weight = $weight;

        return compact('sections', 'biometrics');
    }


    public function carePlanThirdPage(CarePlan $carePlan, User $patient)
    {
        if (empty($carePlan)) return false;

        $cptId = $carePlan->care_plan_template_id;
        $template = CarePlanTemplate::find($cptId);

        $template = $template->loadWithInstructionsAndSort([
                'cpmSymptoms',
            ]);

        //get the User's cpmProblems
        $patientSymptoms = $patient->cpmSymptoms()->get();
        $patientSymptomsIds = $patientSymptoms->lists('id')->all();
        
        $patientMiscs = $patient->cpmMiscs()->get();
        $patientMiscsIds = $patientMiscs->lists('id')->all();

        $symptoms = new Section();
        $symptoms->name = 'cpmSymptoms';
        $symptoms->title = 'Symptoms to Monitor';
        $symptoms->items = $template->cpmSymptoms;
        $symptoms->patientItemIds = $patientSymptomsIds;
        $symptoms->patientItems = $patientSymptoms->keyBy('id');


        $additionalInfo = new Section();
        $additionalInfo->name = 'cpmMiscs';
        $additionalInfo->title = 'Additional Information';
        $additionalInfo->miscs = $template->cpmMiscs()->whereIn('name', [
                CpmMisc::ALLERGIES,
                CpmMisc::APPOINTMENTS,
                CpmMisc::SOCIAL_SERVICES,
                CpmMisc::OTHER,
            ])
            ->orderBy('pivot_ui_sort')->get();
        $additionalInfo->patientMiscsIds = $patientMiscsIds;
        $additionalInfo->patientMiscs = $patientMiscs->keyBy('id');


        //Add sections here in order
        $sections = [
            $symptoms,
            $additionalInfo
        ];

        return compact('sections');
    }

    /**
     * Get the User's Problems to populate the User header
     *
     * @param User $patient
     * @return array
     */
    public function getProblemsToMonitor(User $patient)
    {
        $problems = $patient->cpmProblems()
            ->lists('name', 'cpm_problem_id')
            ->all();
        
        $otherConditions = $patient->cpmMiscs()
            ->where('name', CpmMisc::OTHER_CONDITIONS)
            ->lists('name', 'cpm_misc_id')
            ->all();
        
        return $otherConditions
            ? array_merge($problems, $otherConditions)
            : $problems;
    }
}