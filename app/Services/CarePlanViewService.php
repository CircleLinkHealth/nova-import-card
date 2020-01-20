<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Models\CPM\UI\Biometrics;
use App\Models\CPM\UI\Section;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan as CarePlan;
use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;

/**
 * This Class does the needful to get the data needed for CarePlan Views and feed it to them.
 *
 * Class CarePlanViewService
 */
class CarePlanViewService
{
    public function carePlanFirstPage(
        CarePlan $carePlan,
        User $patient
    ) {
        if (empty($carePlan)) {
            return false;
        }

        $cptId    = $carePlan->care_plan_template_id;
        $template = CarePlanTemplate::find($cptId);

        //This cannot be null because we have a foreign key constraint.
        //If a CarePlanTemplate is deleted all it's CarePlans are also deleted
        //ie. We want an exception to be thrown so keep this commented out, unless it's an emergency.
        //
        // if (empty($template)) abort(404, 'Care Plan Template not found.');

        //get the User's cpmProblems
        $patientProblems    = $patient->cpmProblems;
        $patientProblemsIds = $patientProblems->pluck('id')->all();

        $patientLifestyles    = $patient->cpmLifestyles;
        $patientLifestylesIds = $patientLifestyles->pluck('id')->all();

        $patientMedicationGroups    = $patient->cpmMedicationGroups;
        $patientMedicationGroupsIds = $patientMedicationGroups->pluck('id')->all();

        $patientMiscs    = $patient->cpmMiscs;
        $patientMiscsIds = $patientMiscs->pluck('id')->all();

        $template = $template->loadWithInstructionsAndSort([
            'cpmLifestyles',
            'cpmMedicationGroups',
            'cpmProblems',
        ]);

        $problems                  = new Section();
        $problems->name            = 'cpmProblems';
        $problems->title           = 'Diagnosis / Problems to Monitor';
        $problems->items           = $template->cpmProblems->sortBy('name')->values();
        $problems->patientItemIds  = $patientProblemsIds;
        $problems->patientItems    = $patientProblems->keyBy('id');
        $problems->miscs           = $template->cpmMiscs()->where('name', CpmMisc::OTHER_CONDITIONS)->get();
        $problems->patientMiscsIds = $patientMiscsIds;
        $problems->patientMiscs    = $patientMiscs->keyBy('id');

        $lifestyles                 = new Section();
        $lifestyles->name           = 'cpmLifestyles';
        $lifestyles->title          = 'Lifestyle to Monitor';
        $lifestyles->items          = $template->cpmLifestyles;
        $lifestyles->patientItemIds = $patientLifestylesIds;
        $lifestyles->patientItems   = $patientLifestyles->keyBy('id');

        $medications                  = new Section();
        $medications->name            = 'cpmMedicationGroups';
        $medications->title           = 'Medications to Monitor';
        $medications->items           = $template->cpmMedicationGroups;
        $medications->patientItemIds  = $patientMedicationGroupsIds;
        $medications->patientItems    = $patientMedicationGroups->keyBy('id');
        $medications->miscs           = $template->cpmMiscs()->where('name', CpmMisc::MEDICATION_LIST)->get();
        $medications->patientMiscsIds = $patientMiscsIds;
        $medications->patientMiscs    = $patientMiscs->keyBy('id');

        $sections = [
            $problems,
            $lifestyles,
            $medications,
        ];

        return compact('sections');
    }

    public function carePlanSecondPage(
        CarePlan $carePlan,
        User $patient
    ) {
        if (empty($carePlan)) {
            return false;
        }

        $cptId    = $carePlan->care_plan_template_id;
        $template = CarePlanTemplate::find($cptId);

        $template = $template->loadWithInstructionsAndSort([
            'cpmBiometrics',
        ]);

        $patientMiscs    = $patient->cpmMiscs;
        $patientMiscsIds = $patientMiscs->pluck('id')->all();

        $bloodPressure = $patient->cpmBloodPressure()->firstOrNew(['patient_id' => $patient->id]);
        $bloodSugar    = $patient->cpmBloodSugar()->firstOrNew(['patient_id' => $patient->id]);
        $smoking       = $patient->cpmSmoking()->firstOrNew(['patient_id' => $patient->id]);
        $weight        = $patient->cpmWeight()->firstOrNew(['patient_id' => $patient->id]);

        $patientBiometrics = $patient->cpmBiometrics;

        $biometrics                 = new Section();
        $biometrics->name           = 'cpmBiometrics';
        $biometrics->title          = 'Biometrics to Monitor';
        $biometrics->items          = $template->cpmBiometrics;
        $biometrics->patientItemIds = $patientBiometrics->pluck('id')->all();
        $biometrics->patientItems   = $patientBiometrics->keyBy('id');

        //Add sections here in order
        $sections = [
            $biometrics,
        ];

        $biometrics                = new Biometrics();
        $biometrics->bloodPressure = $bloodPressure;
        $biometrics->bloodSugar    = $bloodSugar;
        $biometrics->smoking       = $smoking;
        $biometrics->weight        = $weight;

        return compact('sections', 'biometrics');
    }

    public function carePlanThirdPage(
        CarePlan $carePlan,
        User $patient
    ) {
        if (empty($carePlan)) {
            return false;
        }

        $cptId    = $carePlan->care_plan_template_id;
        $template = CarePlanTemplate::find($cptId);

        $template = $template->loadWithInstructionsAndSort([
            'cpmSymptoms',
        ]);

        //get the User's cpmProblems
        $patientSymptoms    = $patient->cpmSymptoms;
        $patientSymptomsIds = $patientSymptoms->pluck('id')->all();

        $patientMiscs    = $patient->cpmMiscs;
        $patientMiscsIds = $patientMiscs->pluck('id')->all();

        $symptoms                 = new Section();
        $symptoms->name           = 'cpmSymptoms';
        $symptoms->title          = 'Symptoms to Monitor';
        $symptoms->items          = $template->cpmSymptoms;
        $symptoms->patientItemIds = $patientSymptomsIds;
        $symptoms->patientItems   = $patientSymptoms->keyBy('id');

        $additionalInfo        = new Section();
        $additionalInfo->name  = 'cpmMiscs';
        $additionalInfo->title = 'Additional Information';
        $additionalInfo->miscs = $template->cpmMiscs()->whereIn('name', [
            CpmMisc::ALLERGIES,
            CpmMisc::SOCIAL_SERVICES,
            CpmMisc::OTHER,
        ])
            ->orderBy('pivot_ui_sort')->get();
        $additionalInfo->patientMiscsIds = $patientMiscsIds;
        $additionalInfo->patientMiscs    = $patientMiscs->keyBy('id');

        //Add sections here in order
        $sections = [
            $symptoms,
            $additionalInfo,
        ];

        return compact('sections');
    }

    public function convert_state_to_abbreviation($state_name)
    {
        switch ($state_name) {
            case 'Alabama':
                return 'AL';
                break;
            case 'Alaska':
                return 'AK';
                break;
            case 'Arizona':
                return 'AZ';
                break;
            case 'Arkansas':
                return 'AR';
                break;
            case 'California':
                return 'CA';
                break;
            case 'Colorado':
                return 'CO';
                break;
            case 'Connecticut':
                return 'CT';
                break;
            case 'Delaware':
                return 'DE';
                break;
            case 'Florida':
                return 'FL';
                break;
            case 'Georgia':
                return 'GA';
                break;
            case 'Hawaii':
                return 'HI';
                break;
            case 'Idaho':
                return 'id';
                break;
            case 'Illinois':
                return 'IL';
                break;
            case 'Indiana':
                return 'IN';
                break;
            case 'Iowa':
                return 'IA';
                break;
            case 'Kansas':
                return 'KS';
                break;
            case 'Kentucky':
                return 'KY';
                break;
            case 'Louisana':
                return 'LA';
                break;
            case 'Maine':
                return 'ME';
                break;
            case 'Maryland':
                return 'MD';
                break;
            case 'Massachusetts':
                return 'MA';
                break;
            case 'Michigan':
                return 'MI';
                break;
            case 'Minnesota':
                return 'MN';
                break;
            case 'Mississippi':
                return 'MS';
                break;
            case 'Missouri':
                return 'MO';
                break;
            case 'Montana':
                return 'MT';
                break;
            case 'Nebraska':
                return 'NE';
                break;
            case 'Nevada':
                return 'NV';
                break;
            case 'New Hampshire':
                return 'NH';
                break;
            case 'New Jersey':
                return 'NJ';
                break;
            case 'New Mexico':
                return 'NM';
                break;
            case 'New York':
                return 'NY';
                break;
            case 'North Carolina':
                return 'NC';
                break;
            case 'North Dakota':
                return 'ND';
                break;
            case 'Ohio':
                return 'OH';
                break;
            case 'Oklahoma':
                return 'OK';
                break;
            case 'Oregon':
                return 'OR';
                break;
            case 'Pennsylvania':
                return 'PA';
                break;
            case 'Rhode Island':
                return 'RI';
                break;
            case 'South Carolina':
                return 'SC';
                break;
            case 'South Dakota':
                return 'SD';
                break;
            case 'Tennessee':
                return 'TN';
                break;
            case 'Texas':
                return 'TX';
                break;
            case 'Utah':
                return 'UT';
                break;
            case 'Vermont':
                return 'VT';
                break;
            case 'Virginia':
                return 'VA';
                break;
            case 'Washington':
                return 'WA';
                break;
            case 'Washington D.C.':
                return 'DC';
                break;
            case 'West Virginia':
                return 'WV';
                break;
            case 'Wisconsin':
                return 'WI';
                break;
            case 'Wyoming':
                return 'WY';
                break;
            case 'Alberta':
                return 'AB';
                break;
            case 'British Columbia':
                return 'BC';
                break;
            case 'Manitoba':
                return 'MB';
                break;
            case 'New Brunswick':
                return 'NB';
                break;
            case 'Newfoundland & Labrador':
                return 'NL';
                break;
            case 'Northwest Territories':
                return 'NT';
                break;
            case 'Nova Scotia':
                return 'NS';
                break;
            case 'Nunavut':
                return 'NU';
                break;
            case 'Ontario':
                return 'ON';
                break;
            case 'Prince Edward Island':
                return 'PE';
                break;
            case 'Quebec':
                return 'QC';
                break;
            case 'Saskatchewan':
                return 'SK';
                break;
            case 'Yukon Territory':
                return 'YT';
                break;
            default:
                return $state_name;
        }
    }
}
