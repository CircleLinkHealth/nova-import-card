<?php

namespace App\Services;


use App\TaskRecommendations;

class PersonalizedPreventionPlanPrepareData
{
    const NUTRITION_TITLE = 'Nutrition';
    const TOBACCO_TITLE = 'Tobacco/Smoking';
    const ALCOHOL_TITLE = 'Alcohol';
    const DRUGS_TITLE = 'Recreational Drug Use';
    const PHYSICAL_TITLE = 'Physical Activity';
    const WEIGHT_BMI_TITLE = 'Weight/BMI';
    const SEXUAL_TITLE = 'Sexual Practices';
    const EMOTIONAL_TITLE = 'Emotional Health';
    const FALL_RISK_TITLE = 'Fall Risk';
    const HEARING_TITLE = 'Hearing Impairment';
    const COGNITIVE_TITLE = 'Cognitive Impairment:';
    const ADL_TITLE = 'ADL';
    const VACCINES_TITLE = 'Immunizations/Vaccines:';
    const SCREENINGS_TITLE = 'Screenings:';
    const OTHER_TITLE = 'Other misc:';
    const VITALS_TITLE = 'Vitals';


    /*
     * https://docs.google.com/document/d/1ZC68KlBgKFYZIDd9uTVBAw_P85oO1VjloHw9vZt2Tmc/edit
    each evaluation from this doc belongs to a function
    */
    public function prepareRecommendations($patientPppData)
    {
        $nutritionRecommendations = [
            $title = PersonalizedPreventionPlanPrepareData::NUTRITION_TITLE,
            $image = 'carrot',
            $fruitVeggies = $this->fruitVeggies($patientPppData, $title),
            $wholeGrain = $this->wholeGrain($patientPppData, $title),
            $fattyFriedFoods = $this->fattyFriedFoods($patientPppData, $title),
            $candySugaryBeverages = $this->candySugaryBeverages($patientPppData, $title),
        ];

        $smokingRecommendations = [
            $title = PersonalizedPreventionPlanPrepareData::TOBACCO_TITLE,
            $image = 'cigarette',
            $currentSmoker = $this->currentSmoker($patientPppData, $title),
            $currentSmokerAge = $this->currentSmokerMale($patientPppData, $title),
            $formerSmoker = $this->formerSmoker($patientPppData, $title),
        ];

        $alcoholRecommendations = [
            $title = PersonalizedPreventionPlanPrepareData::ALCOHOL_TITLE,
            $image = 'wine',
            $alcoholUse = $this->alcoholUse($patientPppData, $title),
        ];

        $recreationalDrugsRecommendations = [
            $title = PersonalizedPreventionPlanPrepareData::DRUGS_TITLE,
            $image = 'flower-3',
            $recreationalDrugs = $this->recreationalDrugs($patientPppData, $title),
        ];

        $physicalActivity = [
            $title = PersonalizedPreventionPlanPrepareData::PHYSICAL_TITLE,
            $image = 'dumbell',
            $physicalActivity = $this->physicalActivity($patientPppData, $title),
        ];

        $weightBmi = [
            $title = PersonalizedPreventionPlanPrepareData::WEIGHT_BMI_TITLE,
            $image = 'weight-scale',
            $weightBmiUnderweight = $this->weightBmiUnderweight($patientPppData, $title),
            $weightBmiOverweight = $this->weightBmiOverweight($patientPppData, $title),
        ];

        $sexualPractices = [
            $title = PersonalizedPreventionPlanPrepareData::SEXUAL_TITLE,
            $image = 'hearts',
            $unprotectedSex = $this->unprotectedSex($patientPppData, $title),
            $womanOfReproductiveAge = $this->womanOfReproductiveAge($patientPppData, $title),
        ];

        $emotionalHealth = [
            $title = PersonalizedPreventionPlanPrepareData::EMOTIONAL_TITLE,
            $image = 'happy-face',
            $depression = $this->depression($patientPppData, $title),
        ];

        $fallRisk = [
            $title = PersonalizedPreventionPlanPrepareData::FALL_RISK_TITLE,
            $image = 'patch',
            $patientHasFallen = $this->patientHasFallen($patientPppData, $title),
        ];

        $hearingImpairment = [
            $title = PersonalizedPreventionPlanPrepareData::HEARING_TITLE,
            $image = 'volume-half',
            $patientHasHearingImper = $this->hearingImpairment($patientPppData, $title),
        ];

        $cognitiveImpairment = [
            $title = PersonalizedPreventionPlanPrepareData::COGNITIVE_TITLE,
            $image = 'thought-bubble',
            $mildCognitiveImpairment = $this->mildCognitiveImpairment($patientPppData, $title),
            $modToSevNeurocognitiveImpairment = $this->modToSevNeurocognitiveImpairment($patientPppData, $title),
        ];

        $adl = [
            $title = PersonalizedPreventionPlanPrepareData::ADL_TITLE,
            $image = 'raised-hand',
            $adlWithNoHelp = $this->adlWithNoHelp($patientPppData, $title),
        ];

        $immunizationsVaccines = [
            $title = PersonalizedPreventionPlanPrepareData::VACCINES_TITLE,
            $image = 'syringe',
            $fluInfluenza = $this->fluInfluenza($patientPppData, $title),
            $tetanusDiphtheria = $this->tetanusDiphtheria($patientPppData, $title),
            $chickenPox = $this->chickenPoxVaricella($patientPppData, $title),
            $hepatitisB = $this->hepatitisB($patientPppData, $title),
            $measlesMumpsRubella = $this->measlesMumpsRubella($patientPppData, $title),
            $humanPapillomavirus = $this->humanPapillomavirus($patientPppData, $title),
            $shingles = $this->shingles($patientPppData, $title),
            $pneumococcalVaccine = $this->pneumococcalVaccine($patientPppData, $title),
        ];

        $screenings = [
            $title = PersonalizedPreventionPlanPrepareData::SCREENINGS_TITLE,
            $image = 'clipboard-list',
            $breastCancerMammogram = $this->breastCancerMammogram($patientPppData, $title),
            $cervicalCancerYoung = $this->cervicalCancerYoung($patientPppData, $title),
            $cervicalCancerElder = $this->cervicalCancerElder($patientPppData, $title),
            $prostateCancer = $this->prostateCancer($patientPppData, $title),
            $colorectalCancer = $this->colorectalCancer($patientPppData, $title),
            $skinCancer = $this->skinCancer($patientPppData, $title),

        ];

        $otherMisc = [
            $title = PersonalizedPreventionPlanPrepareData::OTHER_TITLE,
            $image = 'layout-4-blocks',
            $osteoporosis = $this->osteoporosis($patientPppData, $title),
            $glaukoma = $this->glaukoma($patientPppData, $title),
            $diabetes = $this->diabetes($patientPppData, $title),
            $cholesterolDyslipidemia = $this->cholesterolDyslipidemia($patientPppData, $title),
            $noMedicalPowerOfAttorney = $this->noMedicalPowerOfAttorney($patientPppData, $title),
            $livingWill = $this->noLivingWillAdvanceDirective($patientPppData, $title),
        ];

        $recommendationsData = collect([
            'recommendation_tasks' => [
                'nutrition_recommendations' => $nutritionRecommendations,
                'tobacco_smoking_recommendations' => $smokingRecommendations,
                'alcohol_recommendations' => $alcoholRecommendations,
                'recreational_drugs' => $recreationalDrugsRecommendations,
                'physical_activity' => $physicalActivity,
                'weightBmi' => $weightBmi,
                'sexual_practices' => $sexualPractices,
                'emotional_health' => $emotionalHealth,
                'fall_risk' => $fallRisk,
                'hearing_impairment' => $hearingImpairment,
                'cognitive_impairment' => $cognitiveImpairment,
                'adl' => $adl,
                'immunizations_vaccines' => $immunizationsVaccines,
                'screenings' => $screenings,
                'other_misc' => $otherMisc,
            ],
        ]);

        $recommendationTasks = collect();
        foreach ($recommendationsData['recommendation_tasks'] as $key => $tasks) {
            $recommendationTasks[$key] = $tasks;

        }

        $personalizedHealthAdvices = $recommendationTasks->map(function ($recommendation) {

            $tasks = array_slice($recommendation, 2);
            $tableData = [];
            foreach ($tasks as $task) {
                if (!empty($task['report_table_data'])) {
                    $tableData[] = $task['report_table_data'];
                }
            }

            return [
                'title' => $recommendation[0],
                'image' => $recommendation[1],
                'tasks' => $tasks,
                'table_data' => $tableData,
            ];
        });

        return $personalizedHealthAdvices;
    }

    public function fruitVeggies($patientPppData, $title)
    {
        $index = 0;
        $nutritionData['fruit_veggies'] = $this->getStringValue($patientPppData->answers_for_eval, 'fruit_veggies');
        if ($nutritionData['fruit_veggies'] !== '4+') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    private function getStringValue($coll, $key, $default = 'N/A')
    {
        if (!$coll || ProviderReportService::filterAnswer($coll)) {
            return $default;
        }

        if (!isset($coll[$key])) {
            return $default;
        }

        return ProviderReportService::getStringValue($coll[$key], $default);
    }

    public function getTaskRecommendations($title, $index)
    {
        $taskRecommendation = TaskRecommendations::where('title', '=', $title)->first();
        $taskRec = isset($taskRecommendation->data[$index])
            ? $taskRecommendation->data[$index]
            : '';

        return $taskRec;
    }

    public function wholeGrain($patientPppData, $title)
    {
        $index = 1;
        $wholeGrain = [];

        $nutritionData['whole_grain'] = $this->getStringValue($patientPppData->answers_for_eval, 'whole_grain');
        $nutritionData['multipleQuestion16'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $diabetesSelected = $this->checkForConditionSelected($nutritionData, $condition = 'Diabetes',
            $checkInCategory = 'multipleQuestion16');

        if ([$nutritionData['whole_grain'] !== '3-4' || $nutritionData['whole_grain'] !== '5+']
            && $diabetesSelected !== true) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $screenings
     *
     * @param null $condition
     *
     * @param $checkInCategory
     *
     * @return bool
     */
    public function checkForConditionSelected($screenings, $condition, $checkInCategory): bool
    {
        $answers = [];
        $checkInAnswers = $screenings;
        if (is_array($checkInAnswers[$checkInCategory])) {
            $arr = $checkInAnswers[$checkInCategory];
            foreach ($arr as $data) {
                $answers[] = $data['name'];
            }
        }
        return in_array($condition, $answers);
    }

    public function fattyFriedFoods($patientPppData, $title)
    {
        $index = 2;
        $nutritionData['fatty_fried_foods'] = $this->getStringValue($patientPppData->answers_for_eval,
            'fatty_fried_foods');

        if ($nutritionData['fatty_fried_foods'] === '3' || $nutritionData['fatty_fried_foods'] === '4+') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function candySugaryBeverages($patientPppData, $title)
    {
        $index = 3;
        $nutritionData['candy_sugary_beverages'] = $this->getStringValue($patientPppData->answers_for_eval,
            'candy_sugary_beverages');

        if ($nutritionData['candy_sugary_beverages'] !== '0') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function currentSmoker($patientPppData, $title)
    {
        $index = 0;
        $smokingData['current_smoker'] = $this->getStringValue($patientPppData->answers_for_eval,
            'current_smoker');
        $smokingData['smoker_interested_quitting'] = $this->getStringValue($patientPppData->answers_for_eval,
            'smoker_interested_quitting');

        if ($smokingData['current_smoker'] === 'Yes' && $smokingData['smoker_interested_quitting'] !== 'I already quit') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function currentSmokerMale($patientPppData, $title)
    {
        $index = 1;
        $smokingData['current_smoker'] = $this->getStringValue($patientPppData->answers_for_eval, 'current_smoker');
        $smokingData['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $smokingData['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');

        if ($smokingData['current_smoker'] === 'Yes' && $smokingData['sex'] === 'Male' && $smokingData['age'] <= '75' && $smokingData['age'] >= '65') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function formerSmoker($patientPppData, $title)
    {
        $index = 2;
        $smokingData['current_smoker'] = $this->getStringValue($patientPppData->answers_for_eval, 'current_smoker');
        $smokingData['smoker_interested_quitting'] = $this->getStringValue($patientPppData->answers_for_eval,
            'smoker_interested_quitting');

        if ($smokingData['current_smoker'] === 'Yes' && $smokingData['smoker_interested_quitting'] === 'I already quit') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function alcoholUse($patientPppData, $title)
    {
        $index = 0;
        $alcoholData['alcohol_use'] = $this->getStringValue($patientPppData->answers_for_eval, 'alcohol_use');
        $alcoholData['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');

        if ($alcoholData['sex'] === 'Male' && $alcoholData['alcohol_use'] === '14+ drinks/week'
            || $alcoholData['sex'] === 'Female' && $alcoholData['alcohol_use'] === '7-10 drinks per week'
            || $alcoholData['alcohol_use'] === '10-14 drinks per week'
            || $alcoholData['alcohol_use'] === '14+ drinks per week') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function recreationalDrugs($patientPppData, $title)
    {
        $index = 0;
        $recreationalDrugs['recreational_drugs'] = $this->getStringValue($patientPppData->answers_for_eval,
            'recreational_drugs');

        if ($recreationalDrugs['recreational_drugs'] === 'Yes') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function physicalActivity($patientPppData, $title)
    {
        $index = 0;
        $physicalActivity['physical_activity'] = $this->getStringValue($patientPppData->answers_for_eval,
            'physical_activity');

        $physicalActivity['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        if ($physicalActivity['physical_activity'] === 'Never' && $physicalActivity['age'] < '65'
            || $physicalActivity['physical_activity'] === '<3 times a week' && $physicalActivity['age'] < '65') {

            return $this->getTaskRecommendations($title, $index);
        };

        return [];
    }

    public function weightBmiUnderweight($patientPppData, $title)
    {
        $index = 0;
        $weightBmi['bmi'] = $this->getStringValue($patientPppData->answers_for_eval, 'bmi');

        if ($weightBmi['bmi'] <= '13.5') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function weightBmiOverweight($patientPppData, $title)
    {
        $index = 1;
        $weightBmi['bmi'] = $this->getStringValue($patientPppData->answers_for_eval, 'bmi');

        if ($weightBmi['bmi'] >= '25') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function unprotectedSex($patientPppData, $title)
    {
        $index = 0;
        $sexualLife['sexually_active'] = $this->getStringValue($patientPppData->answers_for_eval, 'sexually_active');
        $sexualLife['multiple_partners'] = $this->getStringValue($patientPppData->answers_for_eval,
            'multiple_partners');
        $sexualLife['safe_sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'safe_sex');

        if ($sexualLife['sexually_active'] === 'Yes'
            && $sexualLife['multiple_partners'] === 'Yes'
            && $sexualLife['safe_sex'] === 'Never') {
            return $this->getTaskRecommendations($title, $index);
        } elseif ($sexualLife['sexually_active'] === 'Yes'
            && $sexualLife['multiple_partners'] === 'Yes'
            && $sexualLife['safe_sex'] === 'Sometimes') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];

    }

    public function womanOfReproductiveAge($patientPppData, $title)
    {
        $index = 1;
        $sexualLife['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $sexualLife['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $sexualLife['domestic_violence_screen'] = $this->getStringValue($patientPppData->answers_for_eval,
            'domestic_violence_screen');

        if ($sexualLife['sex'] === 'Female'
            && $sexualLife['domestic_violence_screen'] === 'Never/10+ years ago'
            && '15' <= $sexualLife['age']
            && $sexualLife['age'] <= '44') {
            return $this->getTaskRecommendations($title, $index);
        };

        return [];
    }

    public function depression($patientPppData, $title)
    {
        $index = 0;
        $emotional['emotional'] = $this->getStringValue($patientPppData->answers_for_eval, 'emotional');
        if ($emotional['emotional'] >= '5') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];

    }

    public function patientHasFallen($patientPppData, $title)
    {
        $index = 0;
        $fallRisk['fall_risk'] = $this->getStringValue($patientPppData->answers_for_eval, 'fall_risk');
        if ($fallRisk['fall_risk'] !== 'Yes') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function hearingImpairment($patientPppData, $title)
    {
        $index = 0;
        $hearingImpairment['hearing_impairment'] = $this->getStringValue($patientPppData->answers_for_eval,
            'hearing_impairment');

        if ($hearingImpairment['hearing_impairment'] === 'Yes'
            || $hearingImpairment['hearing_impairment'] === 'Sometimes') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];

    }

    public function mildCognitiveImpairment($patientPppData, $title)
    {
        $index = 0;
        $cognitiveAssessment['cognitive_assessment'] = $this->getStringValue($patientPppData->answers_for_eval,
            'cognitive_assessment');

        if ($cognitiveAssessment['cognitive_assessment'] === '3') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function modToSevNeurocognitiveImpairment($patientPppData, $title)
    {
        $index = 1;
        $cognitiveAssessment['cognitive_assessment'] = $this->getStringValue($patientPppData->answers_for_eval,
            'cognitive_assessment');

        if ($cognitiveAssessment['cognitive_assessment'] !== '3' && $cognitiveAssessment['cognitive_assessment'] !== '4' && $cognitiveAssessment['cognitive_assessment'] !== '5') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function adlWithNoHelp($patientPppData, $title)
    {
        $index = 0;
        $adl['adl'] = $this->getStringValue($patientPppData->answers_for_eval, 'adl');
        $adl['assistance_in_daily_activities'] = $this->getStringValue($patientPppData->answers_for_eval,
            'assistance_in_daily_activities');
        if ($adl['adl'] !== 'N/A'
            && $adl['assistance_in_daily_activities'] === 'No') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function fluInfluenza($patientPppData, $title)
    {
        $index = 0;
        $vaccines['flu_influenza'] = $this->getStringValue($patientPppData->answers_for_eval, 'flu_influenza');
        if ($vaccines['flu_influenza'] === 'No'
            //unsure does not in exist in answers options (HRA Q26.). Im waiting for Raph's feedback on this.
            || $vaccines['flu_influenza'] === 'Unsure') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];

    }

    public function tetanusDiphtheria($patientPppData, $title)
    {
        $index = 1;
        $vaccines['tetanus_diphtheria'] = $this->getStringValue($patientPppData->answers_for_eval,
            'tetanus_diphtheria');
        $vaccines['rubella'] = $this->getStringValue($patientPppData->answers_for_eval, 'rubella');
        if ($vaccines['tetanus_diphtheria'] === 'No'
            || $vaccines['tetanus_diphtheria'] === 'Unsure'
            || $vaccines['rubella'] === 'No'
            || $vaccines['rubella'] === 'Unsure') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function chickenPoxVaricella($patientPppData, $title)
    {
        $index = 2;
        $vaccines['chicken_pox'] = $this->getStringValue($patientPppData->answers_for_eval, 'chicken_pox');
        if ($vaccines['chicken_pox'] === 'No'
            || $vaccines['chicken_pox'] === 'Unsure') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function hepatitisB($patientPppData, $title)
    {
        $index = 3;
        $vaccines['hepatitis_b'] = $this->getStringValue($patientPppData->answers_for_eval, 'hepatitis_b');
        if ($vaccines['hepatitis_b'] === 'No'
            || $vaccines['hepatitis_b'] === 'Unsure') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];

    }

    public function measlesMumpsRubella($patientPppData, $title)
    {
        $index = 4;;
        $vaccines['measles_mumps_rubella'] = $this->getStringValue($patientPppData->answers_for_eval,
            'measles_mumps_rubella');
        if ($vaccines['measles_mumps_rubella'] === 'No'
            || $vaccines['measles_mumps_rubella'] === 'Unsure') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function humanPapillomavirus($patientPppData, $title)
    {
        $index = 5;
        $vaccines['human_papillomavirus'] = $this->getStringValue($patientPppData->answers_for_eval,
            'human_papillomavirus');
        $vaccines['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        if ($vaccines['age'] <= '26'
            && ($vaccines['human_papillomavirus'] === 'No' || $vaccines['human_papillomavirus'] === 'Unsure')) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function shingles($patientPppData, $title)
    {
        $index = 6;
        $vaccines['shingles'] = $this->getStringValue($patientPppData->answers_for_eval, 'shingles');
        $vaccines['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        if ($vaccines['age'] > '50'
            && ($vaccines['shingles'] === 'No' || $vaccines['shingles'] === 'Unsure')) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function pneumococcalVaccine($patientPppData, $title)
    {
        $index = 7;
        $vaccines['pneumococcal_vaccine'] = $this->getStringValue($patientPppData->answers_for_eval,
            'pneumococcal_vaccine');
        if ($vaccines['pneumococcal_vaccine'] === 'No'
            || $vaccines['pneumococcal_vaccine'] === 'Unsure') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function breastCancerMammogram($patientPppData, $title)
    {
        $index = 0;

        $screenings['breast_cancer_screening'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['breast_cancer_screening'])
            ? $patientPppData->answers_for_eval['breast_cancer_screening']
            : 'N/A';
        $screenings['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['family_conditions'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';

        $breastCancerSelected = $this->checkForConditionSelected($screenings, $condition = 'Breast Cancer',
            $checkInCategory = 'family_conditions');

        if ($screenings['sex'] === 'Female' && '50' < $screenings['age'] && $screenings['age'] < '74') {
            return $this->getTaskRecommendations($title, $index);

        } elseIf (!($screenings['breast_cancer_screening'] === 'In the last 2-3 years'
            || $screenings['breast_cancer_screening'] === 'In the last year')
            && $screenings['sex'] === 'Female'
            && $breastCancerSelected === true) {
            return $this->getTaskRecommendations($title, $index);

        } elseIf ($screenings['breast_cancer_screening'] !== 'In the last year'
            && $screenings['sex'] === 'Female'
            && $breastCancerSelected === true) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function cervicalCancerYoung($patientPppData, $title)
    {
        $index = 1;
        $screenings['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['cervical_cancer_screening'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['cervical_cancer_screening'])
            ? $patientPppData->answers_for_eval['cervical_cancer_screening']
            : 'N/A';

        if ($screenings['sex'] === 'Female'
            && '21' <= $screenings['age']
            && $screenings['age'] <= '29'
            && $screenings['cervical_cancer_screening'] !== 'In the last 2-3 years') {
            return $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Female'
            && '21' <= $screenings['age']
            && $screenings['age'] <= '29'
            && $screenings['cervical_cancer_screening'] !== 'In the last year') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function cervicalCancerElder($patientPppData, $title)
    {
        $index = 2;
        $screenings['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['cervical_cancer_screening'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['cervical_cancer_screening'])
            ? $patientPppData->answers_for_eval['cervical_cancer_screening']
            : 'N/A';

        if ($screenings['sex'] === 'Female'
            && '30' <= $screenings['age']
            && $screenings['age'] <= '65'
            && $screenings['cervical_cancer_screening'] === 'In the last 6-10 years') {
            return $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Female'
            && '30' <= $screenings['age']
            && $screenings['age'] <= '65'
            && $screenings['cervical_cancer_screening'] === '10+ years ago/Never/Unsure') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function prostateCancer($patientPppData, $title)
    {
        $index = 3;
        $screenings['race'] = $this->getStringValue($patientPppData->answers_for_eval, 'race');
        $screenings['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['prostate_cancer_screening'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['prostate_cancer_screening'])
            ? $patientPppData->answers_for_eval['prostate_cancer_screening']
            : 'N/A';
        $screenings['multipleQuestion16'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $prostateCancerSelected = $this->checkForConditionSelected($screenings, $condition = 'Prostate Cancer',
            $checkInCategory = 'multipleQuestion16');

        if ($screenings['sex'] === 'Male'
            && '55' <= $screenings['age']
            && $screenings['age'] <= '69'
            && $screenings['prostate_cancer_screening'] === '10+ years ago/Never/Unsure') {
            return $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Male'
            && $screenings['race'] === 'Black/African-Ameri.'
            && $screenings['prostate_cancer_screening'] === '10+ years ago/Never/Unsure') {
            return $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Male' && $prostateCancerSelected === true) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function colorectalCancer($patientPppData, $title)
    {
        $index = 4;
        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['colorectal_cancer_screening'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['colorectal_cancer_screening'])
            ? $patientPppData->answers_for_eval['colorectal_cancer_screening']
            : 'N/A';
        $screenings['family_conditions'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';

        $colorectalCancerSelected = $this->checkForConditionSelected($screenings, $condition = 'Colorectal Cancer',
            $checkInCategory = 'family_conditions');

        if ('50' <= $screenings['age']
            && $screenings['age'] <= '75'
            || $colorectalCancerSelected === true
            || $screenings['colorectal_cancer_screening'] === 'In the last 6-10 years'
            || $screenings['colorectal_cancer_screening'] === 'Never/10 years ago') {

            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function skinCancer($patientPppData, $title)
    {
        $index = 5;
        $screenings['multipleQuestion16'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $screenings['family_conditions'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';

        $screenings['family_members_with_condition'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['family_members_with_condition'])
            ? $patientPppData->answers_for_eval['family_members_with_condition']
            : 'N/A';

        $hasSkinCancerSelectedInQ18 = $this->checkForConditionSelected($screenings, $condition = 'Skin Cancer',
            $checkInCategory = 'family_conditions');

        $checkSkinCancerIsSelectedInQ16 = $this->checkSkinCancerIsSelectedInQ16($screenings,
            'multipleQuestion16',
            'Cancer',
            'Skin');

        $countFamilyMembersWithSkinCancerFromQ18 = $this->countFamilyMembersWithSkinCancer($screenings, $condition = 'Skin Cancer');

        if ($hasSkinCancerSelectedInQ18 === true && $countFamilyMembersWithSkinCancerFromQ18 >= '2' || $checkSkinCancerIsSelectedInQ16 === true) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $screenings
     *
     * @param string $checkInCategory
     * @param string $conditionName
     * @param string $conditionType
     *
     * @return array|bool
     */
    public function checkSkinCancerIsSelectedInQ16(
        $screenings,
        string $checkInCategory,
        string $conditionName,
        string $conditionType
    )
    {
        $checkInAnswers = $screenings;
        if (is_array($checkInAnswers[$checkInCategory])) {
            $arr = $checkInAnswers[$checkInCategory];
            foreach ($arr as $data) {
                if (array_key_exists('type', $data)
                    && isset($data['type'])
                    && array_key_exists('name', $data)
                    && isset($data['name'])
                    && $data['name'] === $conditionName) {
                    return $data['type'] === $conditionType;
                }
            }
        }
        return false;
    }

    /**
     * @param $screenings
     * @param string $condition
     * @return int
     */
    public function countFamilyMembersWithSkinCancer($screenings, $condition): int
    {
        $answers = [];
        $checkInCategory = 'family_members_with_condition';
        $checkInAnswers = $screenings;
        if (is_array($checkInAnswers[$checkInCategory])) {
            foreach ($checkInAnswers[$checkInCategory] as $data) {
                if ($data['name'] === $condition) {
                    $answers = $data['family'];
                }
            }
        }

        return count($answers);

    }

    public function osteoporosis($patientPppData, $title)
    {
        $index = 0;
        $screenings['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');

        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        $screenings['osteoporosis_screening'] = $this->getStringValue($patientPppData->answers_for_eval,
            'osteoporosis_screening');

        $screenings['fall_risk'] = $this->getStringValue($patientPppData->answers_for_eval, 'fall_risk');

        if ($screenings['sex'] === 'Female'
            && $screenings['age'] >= '65'
            && $screenings['osteoporosis_screening'] !== 'In the last year') {
            return $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Male'
            && $screenings['age'] >= '70'
            && $screenings['osteoporosis_screening'] !== 'In the last year') {
            return $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['fall_risk'] === 'Yes'
            && $screenings['osteoporosis_screening'] !== 'In the last year') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function glaukoma($patientPppData, $title)
    {
        $index = 1;
        $screenings['glaukoma_screening'] = $this->getStringValue($patientPppData->answers_for_eval,
            'glaukoma_screening');
        if ($screenings['glaukoma_screening'] !== 'In the last year') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function diabetes($patientPppData, $title)
    {
        $index = 2;
        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['bmi'] = $this->getStringValue($patientPppData->answers_for_eval, 'bmi');

        if ($screenings['age'] > '20' && $screenings['bmi'] >= '25') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function cholesterolDyslipidemia($patientPppData, $title)
    {
        $index = 3;
        $screenings['blood_pressure'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['blood_pressure'])
            ? $patientPppData->answers_for_eval['blood_pressure']
            : 'N/A';
        $screenings['bmi'] = $this->getStringValue($patientPppData->answers_for_eval, 'bmi');
        $screenings['multipleQuestion16'] = !ProviderReportService::filterAnswer($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';
        $screenings['current_smoker'] = $this->getStringValue($patientPppData->answers_for_eval, 'current_smoker');
        $screenings['physical_activity'] = $this->getStringValue($patientPppData->answers_for_eval,
            'physical_activity');
        $screenings['fatty_fried_foods'] = $this->getStringValue($patientPppData->answers_for_eval,
            'fatty_fried_foods');

        $diabetesSelected = $this->checkForConditionSelected($screenings,
            'Diabetes',
            'multipleQuestion16');

        $highBloodPressure = $this->checkForHighBloodPressure($screenings,
            'blood_pressure',
            '130',
            '80');

        if ($highBloodPressure === 'true'
            || $screenings['bmi'] >= '30'
            || $diabetesSelected === true
            || $screenings['current_smoker'] === 'Yes'
            || $screenings['physical_activity'] === '<3 times a week'
            || $screenings['physical_activity'] === 'Never'
            || $screenings['fatty_fried_foods'] !== '0'
            || $screenings['fatty_fried_foods'] !== '1') {

            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $screenings
     * @param string $checkInCategory
     * @param string $conditionFirstMetric
     * @param string $conditionSecondMetric
     *
     * @return array|bool
     */
    public function checkForHighBloodPressure(
        $screenings,
        string $checkInCategory,
        string $conditionFirstMetric,
        string $conditionSecondMetric
    )
    {

        $checkInAnswers = $screenings;
        $data = $checkInAnswers[$checkInCategory];
        if ($data['first_metric'] >= $conditionFirstMetric
            && $data['second_metric'] >= $conditionSecondMetric) {
            return true;
        }
        return false;

    }

    public function noMedicalPowerOfAttorney($patientPppData, $title)
    {

        $index = 4;
        $screenings['medical_attonery'] = $this->getStringValue($patientPppData->answers_for_eval, 'medical_attonery');

        if ($screenings['medical_attonery'] === 'No') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function noLivingWillAdvanceDirective($patientPppData, $title)
    {
        $index = 5;
        $screenings['living_will'] = $this->getStringValue($patientPppData->answers_for_eval, 'living_will');

        if ($screenings['living_will'] === 'No') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }
}
