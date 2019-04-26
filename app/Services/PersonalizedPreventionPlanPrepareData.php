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


    /*
     * https://docs.google.com/document/d/1ZC68KlBgKFYZIDd9uTVBAw_P85oO1VjloHw9vZt2Tmc/edit
    each evaluation from this doc belongs to a function
    */
    public function prepareRecommendations($patientPppData)
    {
        $nutritionRecommendations = [
            $title = PersonalizedPreventionPlanPrepareData::NUTRITION_TITLE,
            $fruitVeggies = $this->fruitVeggies($patientPppData, $title),
            $wholeGrain = $this->wholeGrain($patientPppData, $title),
            $fattyFriedFoods = $this->fattyFriedFoods($patientPppData, $title),
            $candySugaryBeverages = $this->candySugaryBeverages($patientPppData, $title),
        ];

        $smokingRecommendations = [
            $title = PersonalizedPreventionPlanPrepareData::TOBACCO_TITLE,
            $currentSmoker = $this->currentSmoker($patientPppData, $title),
            $currentSmokerAge = $this->currentSmokerMale($patientPppData, $title),
            $formerSmoker = $this->formerSmoker($patientPppData, $title),
        ];

        $alcoholRecommendations = [
            $title = PersonalizedPreventionPlanPrepareData::ALCOHOL_TITLE,
            $alcoholUse = $this->alcoholUse($patientPppData, $title),
        ];

        $recreationalDrugsRecommendations = [
            $title = PersonalizedPreventionPlanPrepareData::DRUGS_TITLE,
            $recreationalDrugs = $this->recreationalDrugs($patientPppData, $title),
        ];

        $physicalActivity = [
            $title = PersonalizedPreventionPlanPrepareData::PHYSICAL_TITLE,
            $physicalActivity = $this->physicalActivity($patientPppData, $title),
        ];

        $weightBmi = [
            $title = PersonalizedPreventionPlanPrepareData::WEIGHT_BMI_TITLE,
            $weightBmiUnderweight = $this->weightBmiUnderweight($patientPppData, $title),
        ];

        $sexualPractices = [
            $title = PersonalizedPreventionPlanPrepareData::SEXUAL_TITLE,
            $unprotectedSex = $this->unprotectedSex($patientPppData, $title),
            $womanOfReproductiveAge = $this->womanOfReproductiveAge($patientPppData, $title),
        ];

        $emotionalHealth = [
            $title = PersonalizedPreventionPlanPrepareData::EMOTIONAL_TITLE,
            $depression = $this->depression($patientPppData, $title),
        ];

        $fallRisk = [
            $title = PersonalizedPreventionPlanPrepareData::FALL_RISK_TITLE,
            $patientHasFallen = $this->patientHasFallen($patientPppData, $title),
        ];

        $hearingImpairment = [
            $title = PersonalizedPreventionPlanPrepareData::HEARING_TITLE,
            $patientHasHearingImper = $this->hearingImpairment($patientPppData, $title),
        ];

        $cognitiveImpairment = [
            $title = PersonalizedPreventionPlanPrepareData::COGNITIVE_TITLE,
            $mildCognitiveImpairment = $this->mildCognitiveImpairment($patientPppData, $title),
            $modToSevNeurocognitiveImpairment = $this->modToSevNeurocognitiveImpairment($patientPppData, $title),
        ];

        $adl = [
            $title = PersonalizedPreventionPlanPrepareData::ADL_TITLE,
            $adlWithNoHelp = $this->adlWithNoHelp($patientPppData, $title),
        ];

        $immunizationsVaccines = [
            $title = PersonalizedPreventionPlanPrepareData::VACCINES_TITLE,
            $fluInfluenza = $this->fluInfluenza($patientPppData, $title),
            $tetanusDiphtheria = $this->tetanusDiphtheria($patientPppData, $title),
            $chickenPox = $this->chickenPoxVaricella($patientPppData, $title),
            $hepatitisB = $this->hepatitisB($patientPppData, $title),
            $measlesMumpsRubella = $this->measlesMumpsRubella($patientPppData, $title),
            $humanPapillomavirus = $this->humanPapillomavirus($patientPppData, $title),
            $shingles = $this->shingles($patientPppData, $title),
            $pneumococcalVaccine = $this->pneumococcalVaccine($patientPppData, $title),
        ];
        $screenings            = [
            $title = PersonalizedPreventionPlanPrepareData::SCREENINGS_TITLE,
            $breastCancerMammogram = $this->breastCancerMammogram($patientPppData, $title),
            $cervicalCancerYoung = $this->cervicalCancerYoung($patientPppData, $title),
            $cervicalCancerElder = $this->cervicalCancerElder($patientPppData, $title),
            $prostateCancer = $this->prostateCancer($patientPppData, $title),
            $colorectalCancer = $this->colorectalCancer($patientPppData, $title),
            $skinCancer = $this->skinCancer($patientPppData, $title),

        ];

        $otherMisc = [
            $title = PersonalizedPreventionPlanPrepareData::OTHER_TITLE,
            $osteoporosis = $this->osteoporosis($patientPppData, $title),
            $glaukoma = $this->glaukoma($patientPppData, $title),
            $diabetes = $this->diabetes($patientPppData, $title),
            $cholesterolDyslipidemia = $this->cholesterolDyslipidemia($patientPppData, $title),
            $noMedicalPowerOfAttorney = $this->noMedicalPowerOfAttorney($patientPppData, $title),
            $livingWill = $this->noLivingWillAdvanceDirective($patientPppData, $title),
        ];

        $recommendationsData = collect([
            'recommendation_tasks' => [
                'nutrition_recommendations'       => $nutritionRecommendations,
                'tobacco_smoking_recommendations' => $smokingRecommendations,
                'alcohol_recommendations'         => $alcoholRecommendations,
                'recreational_drugs'              => $recreationalDrugsRecommendations,
                'physical_activity'               => $physicalActivity,
                'weightBmi'                       => $weightBmi,
                'sexual_practices'                => $sexualPractices,
                'emotional_health'                => $emotionalHealth,
                'fall_risk'                       => $fallRisk,
                'hearing_impairment'              => $hearingImpairment,
                'cognitive_impairment'            => $cognitiveImpairment,
                'adl'                             => $adl,
                'immunizations_vaccines'          => $immunizationsVaccines,
                'screenings'                      => $screenings,
                'other_misc'                      => $otherMisc,
            ],
        ]);

        $recommendationTasks = collect();
        foreach ($recommendationsData['recommendation_tasks'] as $key => $tasks) {
            $recommendationTasks[$key] = $tasks;
        }

        $personalizedHealthAdvices = $recommendationTasks->map(function ($recommendation) {
            $tasks     = array_slice($recommendation, 1);
            $tableData = [];
            foreach ($tasks as $task) {
                if ( ! empty($task['report_table_data'])) {
                    $tableData = $task['report_table_data'];
                }
            }

            return [
                'title'      => $recommendation[0],
                'tasks'      => $tasks,
                'table_data' => $tableData,
            ];
        });

        return $personalizedHealthAdvices;
    }

    public function fruitVeggies($patientPppData, $title)
    {
        $index                          = 0;
        $fruitVeggies                   = [];
        $nutritionData['fruit_veggies'] = ! empty($patientPppData->answers_for_eval['fruit_veggies'])
            ? $patientPppData->answers_for_eval['fruit_veggies']
            : 'N/A';
        if ($nutritionData['fruit_veggies'] !== '4+') {
            $fruitVeggies = $this->getTaskRecommendations($title, $index);
        }

        return $fruitVeggies;
    }

    public function getTaskRecommendations($title, $index)
    {
        $taskRecommendation = TaskRecommendations::where('title', '=', $title)->first();
        $recommendation     = [];
        foreach ($taskRecommendation->data as $item) {

            $recommendation[] = $item;
        }
        $nutritionRec = $recommendation[$index];

        return $nutritionRec;
    }

    public function wholeGrain($patientPppData, $title)
    {
        $index                        = 1;
        $wholeGrain                   = [];
        $nutritionData['whole_grain'] = ! empty($patientPppData->answers_for_eval['whole_grain'])
            ? $patientPppData->answers_for_eval['whole_grain']
            : 'N/A';

        $nutritionData['multipleQuestion16'] = ! empty($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $diabetesSelected = $this->checkForConditionSelected($nutritionData, $condition = 'Diabetes',
            $checkInCategory = 'multipleQuestion16');

        if ([$nutritionData['whole_grain'] !== '3-4' || $nutritionData['whole_grain'] !== '5+']
            && $diabetesSelected !== true) {
            $wholeGrain = $this->getTaskRecommendations($title, $index);
        }

        return $wholeGrain;
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
        $answers        = [];
        $checkInAnswers = $screenings;

        foreach ($checkInAnswers[$checkInCategory] as $data) {
            $answers[] = $data['name'];
        }

        if (in_array($condition, $answers)) {
            $conditionIsSelectedAsAnswer = true;
        } else {
            $conditionIsSelectedAsAnswer = false;
        }

        return $conditionIsSelectedAsAnswer;
    }

    public function fattyFriedFoods($patientPppData, $title)
    {
        $index           = 2;
        $fattyFriedFoods = [];

        $nutritionData['fatty_fried_foods'] = ! empty($patientPppData->answers_for_eval['fatty_fried_foods'])
            ? $patientPppData->answers_for_eval['fatty_fried_foods']
            : 'N/A';
        if ($nutritionData['fatty_fried_foods'] === '3' || $nutritionData['fatty_fried_foods'] === '4+') {
            $fattyFriedFoods = $this->getTaskRecommendations($title, $index);
        }

        return $fattyFriedFoods;
    }

    public function candySugaryBeverages($patientPppData, $title)
    {
        $index                                   = 3;
        $candySugaryBeverages                    = [];
        $nutritionData['candy_sugary_beverages'] = ! empty($patientPppData->answers_for_eval['candy_sugary_beverages'])
            ? $patientPppData->answers_for_eval['candy_sugary_beverages']
            : 'N/A';
        if ($nutritionData['candy_sugary_beverages'] !== '0') {
            $candySugaryBeverages = $this->getTaskRecommendations($title, $index);
        }

        return $candySugaryBeverages;
    }

    public function currentSmoker($patientPppData, $title)
    {
        $index                         = 0;
        $currentSmoker                 = [];
        $smokingData['current_smoker'] = ! empty($patientPppData->answers_for_eval['current_smoker'])
            ? $patientPppData->answers_for_eval['current_smoker']
            : 'N/A';

        $smokingData['smoker_interested_quitting'] = ! empty($patientPppData->answers_for_eval['smoker_interested_quitting'])
            ? $patientPppData->answers_for_eval['smoker_interested_quitting']
            : 'N/A';

        if ($smokingData['current_smoker'] === 'Yes' && $smokingData['smoker_interested_quitting'] !== 'I already quit') {
            $currentSmoker = $this->getTaskRecommendations($title, $index);
        }

        return $currentSmoker;
    }

    public function currentSmokerMale($patientPppData, $title)
    {
        $index                         = 1;
        $currentSmokerAge              = [];
        $smokingData['current_smoker'] = ! empty($patientPppData->answers_for_eval['current_smoker'])
            ? $patientPppData->answers_for_eval['current_smoker']
            : 'N/A';

        $smokingData['age'] = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';

        $smokingData['sex'] = ! empty($patientPppData->answers_for_eval['sex'])
            ? $patientPppData->answers_for_eval['sex']
            : 'N/A';

        if ($smokingData['current_smoker'] === 'Yes' && $smokingData['sex'] === 'Male' && $smokingData['age'] <= '75' && $smokingData['age'] >= '65') {
            $currentSmokerAge = $this->getTaskRecommendations($title, $index);
        }

        return $currentSmokerAge;
    }

    public function formerSmoker($patientPppData, $title)
    {
        $index                         = 2;
        $formerSmoker                  = [];
        $smokingData['current_smoker'] = ! empty($patientPppData->answers_for_eval['current_smoker'])
            ? $patientPppData->answers_for_eval['current_smoker']
            : 'N/A';

        $smokingData['smoker_interested_quitting'] = ! empty($patientPppData->answers_for_eval['smoker_interested_quitting'])
            ? $patientPppData->answers_for_eval['smoker_interested_quitting']
            : 'N/A';

        if ($smokingData['current_smoker'] === 'Yes' && $smokingData['smoker_interested_quitting'] === 'I already quit') {
            $formerSmoker = $this->getTaskRecommendations($title, $index);
        }

        return $formerSmoker;
    }

    public function alcoholUse($patientPppData, $title)
    {
        $index                      = 0;
        $alcoholUse                 = [];
        $alcoholData['alcohol_use'] = ! empty($patientPppData->answers_for_eval['alcohol_use'])
            ? $patientPppData->answers_for_eval['alcohol_use']
            : 'N/A';

        $alcoholData['sex'] = ! empty($patientPppData->answers_for_eval['sex'])
            ? $patientPppData->answers_for_eval['sex']
            : 'N/A';

        if ($alcoholData['sex'] === 'Male' && $alcoholData['alcohol_use'] === '14+ drinks/week'
            || $alcoholData['sex'] === 'Female' && $alcoholData['alcohol_use'] === '7-10 drinks per week'
            || $alcoholData['alcohol_use'] === '10-14 drinks per week'
            || $alcoholData['alcohol_use'] === '14+ drinks per week') {
            $alcoholUse = $this->getTaskRecommendations($title, $index);
        }

        return $alcoholUse;
    }

    public function recreationalDrugs($patientPppData, $title)
    {
        $index                                   = 0;
        $recreationalDrugs                       = [];
        $recreationalDrugs['recreational_drugs'] = ! empty($patientPppData->answers_for_eval['recreational_drugs'])
            ? $patientPppData->answers_for_eval['recreational_drugs']
            : 'N/A';

        if ($recreationalDrugs['recreational_drugs'] === 'Yes') {
            $recreationalDrugs = $this->getTaskRecommendations($title, $index);
        }

        return $recreationalDrugs;
    }

    public function physicalActivity($patientPppData, $title)
    {
        $index                                 = 0;
        $physicalActivity                      = [];
        $physicalActivity['physical_activity'] = ! empty($patientPppData->answers_for_eval['physical_activity'])
            ? $patientPppData->answers_for_eval['physical_activity']
            : 'N/A';

        $physicalActivity['age'] = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';

        if ($physicalActivity['physical_activity'] === 'Never' && $physicalActivity['age'] < '65'
            || $physicalActivity['physical_activity'] === '<3 times a week' && $physicalActivity['age'] < '65') {

            $physicalActivity = $this->getTaskRecommendations($title, $index);
        };

        return $physicalActivity;
    }

    public function weightBmiUnderweight($patientPppData, $title)
    {
        $index                = 0;
        $weightBmiUnderweight = [];
        $weightBmi['bmi']     = ! empty($patientPppData->answers_for_eval['bmi'])
            ? $patientPppData->answers_for_eval['bmi']
            : 'N/A';

        if ($weightBmi['bmi'] <= '13.5') {
            $weightBmiUnderweight = $this->getTaskRecommendations($title, $index);
        }

        return $weightBmiUnderweight;
    }

    public function unprotectedSex($patientPppData, $title)
    {
        $index                           = 0;
        $unprotectedSex                  = [];
        $sexualLife['sexually_active']   = ! empty($patientPppData->answers_for_eval['sexually_active'])
            ? $patientPppData->answers_for_eval['sexually_active']
            : 'N/A';
        $sexualLife['multiple_partners'] = ! empty($patientPppData->answers_for_eval['multiple_partners'])
            ? $patientPppData->answers_for_eval['multiple_partners']
            : 'N/A';
        $sexualLife['safe_sex']          = ! empty($patientPppData->answers_for_eval['safe_sex'])
            ? $patientPppData->answers_for_eval['safe_sex']
            : 'N/A';

        if ($sexualLife['sexually_active'] === 'Yes'
            && $sexualLife['multiple_partners'] === 'Yes'
            && $sexualLife['safe_sex'] === 'Never') {
            $unprotectedSex = $this->getTaskRecommendations($title, $index);
        } elseif ($sexualLife['sexually_active'] === 'Yes'
                  && $sexualLife['multiple_partners'] === 'Yes'
                  && $sexualLife['safe_sex'] === 'Sometimes') {
            $unprotectedSex = $this->getTaskRecommendations($title, $index);
        }

        return $unprotectedSex;

    }

    public function womanOfReproductiveAge($patientPppData, $title)
    {
        $index                                  = 1;
        $womanOfReproductiveAge                 = [];
        $sexualLife['sex']                      = ! empty($patientPppData->answers_for_eval['sex'])
            ? $patientPppData->answers_for_eval['sex']
            : 'N/A';
        $sexualLife['age']                      = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';
        $sexualLife['domestic_violence_screen'] = ! empty($patientPppData->answers_for_eval['domestic_violence_screen'])
            ? $patientPppData->answers_for_eval['domestic_violence_screen']
            : 'N/A';

        if ($sexualLife['sex'] === 'Female'
            && $sexualLife['domestic_violence_screen'] === 'Never/10+ years ago'
            && '15' <= $sexualLife['age']
            && $sexualLife['age'] <= '44') {
            $womanOfReproductiveAge = $this->getTaskRecommendations($title, $index);
        };

        return $womanOfReproductiveAge;
    }

    public function depression($patientPppData, $title)
    {
        $index                  = 0;
        $depression             = [];
        $emotional['emotional'] = ! empty($patientPppData->answers_for_eval['emotional'])
            ? $patientPppData->answers_for_eval['emotional']
            : 'N/A';
        if ($emotional['emotional'] >= '5') {
            $depression = $this->getTaskRecommendations($title, $index);
        }

        return $depression;

    }

    public function patientHasFallen($patientPppData, $title)
    {
        $index                 = 0;
        $patientHasFallen      = [];
        $fallRisk['fall_risk'] = ! empty($patientPppData->answers_for_eval['fall_risk'])
            ? $patientPppData->answers_for_eval['fall_risk']
            : 'N/A';
        if ($fallRisk['fall_risk'] !== 'Yes') {
            $patientHasFallen = $this->getTaskRecommendations($title, $index);
        }

        return $patientHasFallen;
    }

    public function hearingImpairment($patientPppData, $title)
    {
        $index                                   = 0;
        $patientHasHearingImper                  = [];
        $hearingImpairment['hearing_impairment'] = ! empty($patientPppData->answers_for_eval['hearing_impairment'])
            ? $patientPppData->answers_for_eval['hearing_impairment']
            : 'N/A';

        if ($hearingImpairment['hearing_impairment'] === 'Yes'
            || $hearingImpairment['hearing_impairment'] === 'Sometimes') {
            $patientHasHearingImper = $this->getTaskRecommendations($title, $index);
        }

        return $patientHasHearingImper;

    }

    public function mildCognitiveImpairment($patientPppData, $title)
    {
        $index                                       = 0;
        $mildCognitiveImpairment                     = [];
        $cognitiveAssessment['cognitive_assessment'] = ! empty($patientPppData->answers_for_eval['cognitive_assessment'])
            ? $patientPppData->answers_for_eval['cognitive_assessment']
            : 'N/A';

        if ($cognitiveAssessment['cognitive_assessment'] === '3') {
            $mildCognitiveImpairment = $this->getTaskRecommendations($title, $index);
        }

        return $mildCognitiveImpairment;
    }

    public function modToSevNeurocognitiveImpairment($patientPppData, $title)
    {
        $index                            = 1;
        $modToSevNeurocognitiveImpairment = [];

        $cognitiveAssessment['cognitive_assessment'] = ! empty($patientPppData->answers_for_eval['cognitive_assessment'])
            ? $patientPppData->answers_for_eval['cognitive_assessment']
            : 'N/A';

        if ($cognitiveAssessment['cognitive_assessment'] !== '3') {
            $modToSevNeurocognitiveImpairment = $this->getTaskRecommendations($title, $index);
        }

        return $modToSevNeurocognitiveImpairment;
    }

    public function adlWithNoHelp($patientPppData, $title)
    {
        $index         = 0;
        $adlWithNoHelp = [];

        $adl['adl']                            = ! empty($patientPppData->answers_for_eval['adl'])
            ? $patientPppData->answers_for_eval['adl']
            : 'N/A';
        $adl['assistance_in_daily_activities'] = ! empty($patientPppData->answers_for_eval['assistance_in_daily_activities'])
            ? $patientPppData->answers_for_eval['assistance_in_daily_activities']
            : 'N/A';
        if ($adl['adl'] !== 'N/A'
            && $adl['assistance_in_daily_activities'] === 'No') {
            $adlWithNoHelp = $this->getTaskRecommendations($title, $index);
        }

        return $adlWithNoHelp;
    }

    public function fluInfluenza($patientPppData, $title)
    {
        $index        = 0;
        $fluInfluenza = [];

        $vaccines['flu_influenza'] = ! empty($patientPppData->answers_for_eval['flu_influenza'])
            ? $patientPppData->answers_for_eval['flu_influenza']
            : 'N/A';
        if ($vaccines['flu_influenza'] === 'No'
            || $vaccines['flu_influenza'] === 'Unsure') {
            $fluInfluenza = $this->getTaskRecommendations($title, $index);
        }

        return $fluInfluenza;

    }

    public function tetanusDiphtheria($patientPppData, $title)
    {
        $index             = 1;
        $tetanusDiphtheria = [];

        $vaccines['tetanus_diphtheria'] = ! empty($patientPppData->answers_for_eval['tetanus_diphtheria'])
            ? $patientPppData->answers_for_eval['tetanus_diphtheria']
            : 'N/A';
        $vaccines['rubella']            = ! empty($patientPppData->answers_for_eval['rubella'])
            ? $patientPppData->answers_for_eval['rubella']
            : 'N/A';
        if ($vaccines['tetanus_diphtheria'] === 'No'
            || $vaccines['tetanus_diphtheria'] === 'Unsure'
            || $vaccines['rubella'] === 'No'
            || $vaccines['rubella'] === 'Unsure') {
            $tetanusDiphtheria = $this->getTaskRecommendations($title, $index);
        }

        return $tetanusDiphtheria;
    }

    public function chickenPoxVaricella($patientPppData, $title)
    {
        $index      = 2;
        $chickenPox = [];

        $vaccines['chicken_pox'] = ! empty($patientPppData->answers_for_eval['chicken_pox'])
            ? $patientPppData->answers_for_eval['chicken_pox']
            : 'N/A';
        if ($vaccines['chicken_pox'] === 'No'
            || $vaccines['chicken_pox'] === 'Unsure') {
            $chickenPox = $this->getTaskRecommendations($title, $index);
        }

        return $chickenPox;
    }

    public function hepatitisB($patientPppData, $title)
    {
        $index                   = 3;
        $hepatitisB              = [];
        $vaccines['hepatitis_b'] = ! empty($patientPppData->answers_for_eval['hepatitis_b'])
            ? $patientPppData->answers_for_eval['hepatitis_b']
            : 'N/A';
        if ($vaccines['hepatitis_b'] === 'No'
            || $vaccines['hepatitis_b'] === 'Unsure') {
            $hepatitisB = $this->getTaskRecommendations($title, $index);
        }

        return $hepatitisB;

    }

    public function measlesMumpsRubella($patientPppData, $title)
    {
        $index                             = 4;
        $measlesMumpsRubella               = [];
        $vaccines['measles_mumps_rubella'] = ! empty($patientPppData->answers_for_eval['measles_mumps_rubella'])
            ? $patientPppData->answers_for_eval['measles_mumps_rubella']
            : 'N/A';
        if ($vaccines['measles_mumps_rubella'] === 'No'
            || $vaccines['measles_mumps_rubella'] === 'Unsure') {
            $measlesMumpsRubella = $this->getTaskRecommendations($title, $index);
        }

        return $measlesMumpsRubella;
    }

    public function humanPapillomavirus($patientPppData, $title)
    {
        $index                            = 5;
        $humanPapillomavirus              = [];
        $vaccines['human_papillomavirus'] = ! empty($patientPppData->answers_for_eval['human_papillomavirus'])
            ? $patientPppData->answers_for_eval['human_papillomavirus']
            : 'N/A';
        $vaccines['age']                  = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';
        if ($vaccines['age'] <= '26'
            && $vaccines['human_papillomavirus'] === 'No') {
            $humanPapillomavirus = $this->getTaskRecommendations($title, $index);
        }elseif ($vaccines['age'] <= '26'
                 && $vaccines['human_papillomavirus'] === 'Unsure'){
            $humanPapillomavirus = $this->getTaskRecommendations($title, $index);
        }

        return $humanPapillomavirus;
    }

    public function shingles($patientPppData, $title)
    {
        $index                = 6;
        $shingles             = [];
        $vaccines['shingles'] = ! empty($patientPppData->answers_for_eval['shingles'])
            ? $patientPppData->answers_for_eval['shingles']
            : 'N/A';
        $vaccines['age']      = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';

        if ($vaccines['age'] > '50'
            && $vaccines['shingles'] === 'No'
            || $vaccines['shingles'] === 'Unsure') {
            $shingles = $this->getTaskRecommendations($title, $index);
        }elseif ($vaccines['age'] > '50'
                 && $vaccines['shingles'] === 'Unsure'){
            $shingles = $this->getTaskRecommendations($title, $index);
        }
        return $shingles;
    }

    public function pneumococcalVaccine($patientPppData, $title)
    {
        $index                            = 7;
        $pneumococcal                     = [];
        $vaccines['pneumococcal_vaccine'] = ! empty($patientPppData->answers_for_eval['pneumococcal_vaccine'])
            ? $patientPppData->answers_for_eval['pneumococcal_vaccine']
            : 'N/A';
        if ($vaccines['pneumococcal_vaccine'] === 'No'
            || $vaccines['pneumococcal_vaccine'] === 'Unsure') {
            $pneumococcal = $this->getTaskRecommendations($title, $index);
        }

        return $pneumococcal;
    }

    public function breastCancerMammogram($patientPppData, $title)
    {
        $index                                 = 0;
        $breastCancerMammogram                 = [];
        $screenings['breast_cancer_screening'] = ! empty($patientPppData->answers_for_eval['breast_cancer_screening'])
            ? $patientPppData->answers_for_eval['breast_cancer_screening']
            : 'N/A';
        $screenings['sex']                     = ! empty($patientPppData->answers_for_eval['sex'])
            ? $patientPppData->answers_for_eval['sex']
            : 'N/A';
        $screenings['age']                     = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';
        $screenings['family_conditions']       = ! empty($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';

        $breastCancerSelected = $this->checkForConditionSelected($screenings, $condition = 'Breast Cancer',
            $checkInCategory = 'family_conditions');

        if ($screenings['sex'] === 'Female' && '50' < $screenings['age'] && $screenings['age'] < '74') {
            $breastCancerMammogram = $this->getTaskRecommendations($title, $index);

        } elseIf ($screenings['breast_cancer_screening'] !== 'In the last 2-3 years'
                  || $screenings['breast_cancer_screening'] !== 'In the last year'
                     && $screenings['sex'] === 'Female'
                     && $breastCancerSelected === true) {
            $breastCancerMammogram = $this->getTaskRecommendations($title, $index);

        } elseIf ($screenings['breast_cancer_screening'] !== 'In the last year'
                  && $screenings['sex'] === 'Female'
                  && $breastCancerSelected === true) {
            $breastCancerMammogram = $this->getTaskRecommendations($title, $index);
        }

        return $breastCancerMammogram;
    }

    public function cervicalCancerYoung($patientPppData, $title)
    {
        $index               = 1;
        $cervicalCancerYoung = [];

        $screenings['sex']                       = ! empty($patientPppData->answers_for_eval['sex'])
            ? $patientPppData->answers_for_eval['sex']
            : 'N/A';
        $screenings['age']                       = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';
        $screenings['cervical_cancer_screening'] = ! empty($patientPppData->answers_for_eval['cervical_cancer_screening'])
            ? $patientPppData->answers_for_eval['cervical_cancer_screening']
            : 'N/A';

        if ($screenings['sex'] === 'Female'
            && '21' <= $screenings['age']
            && $screenings['age'] <= '29'
            && $screenings['cervical_cancer_screening'] !== 'In the last 2-3 years') {
            $cervicalCancerYoung = $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Female'
                  && '21' <= $screenings['age']
                  && $screenings['age'] <= '29'
                  && $screenings['cervical_cancer_screening'] !== 'In the last year') {
            $cervicalCancerYoung = $this->getTaskRecommendations($title, $index);
        }

        return $cervicalCancerYoung;
    }

    public function cervicalCancerElder($patientPppData, $title)
    {
        $index               = 2;
        $cervicalCancerElder = [];

        $screenings['sex']                       = ! empty($patientPppData->answers_for_eval['sex'])
            ? $patientPppData->answers_for_eval['sex']
            : 'N/A';
        $screenings['age']                       = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';
        $screenings['cervical_cancer_screening'] = ! empty($patientPppData->answers_for_eval['cervical_cancer_screening'])
            ? $patientPppData->answers_for_eval['cervical_cancer_screening']
            : 'N/A';

        if ($screenings['sex'] === 'Female'
            && '30' <= $screenings['age']
            && $screenings['age'] <= '65'
            && $screenings['cervical_cancer_screening'] === 'In the last 6-10 years') {
            $cervicalCancerElder = $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Female'
                  && '30' <= $screenings['age']
                  && $screenings['age'] <= '65'
                  && $screenings['cervical_cancer_screening'] === '10+ years ago/Never/Unsure') {
            $cervicalCancerElder = $this->getTaskRecommendations($title, $index);
        }

        return $cervicalCancerElder;
    }

    public function prostateCancer($patientPppData, $title)
    {
        $index                                   = 3;
        $prostateCancer                          = [];
        $screenings['race']                      = ! empty($patientPppData->answers_for_eval['race'])
            ? $patientPppData->answers_for_eval['race']
            : 'N/A';
        $screenings['sex']                       = ! empty($patientPppData->answers_for_eval['sex'])
            ? $patientPppData->answers_for_eval['sex']
            : 'N/A';
        $screenings['age']                       = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';
        $screenings['prostate_cancer_screening'] = ! empty($patientPppData->answers_for_eval['prostate_cancer_screening'])
            ? $patientPppData->answers_for_eval['prostate_cancer_screening']
            : 'N/A';

        $screenings['multipleQuestion16'] = ! empty($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $prostateCancerSelected = $this->checkForConditionSelected($screenings, $condition = 'Prostate Cancer',
            $checkInCategory = 'multipleQuestion16');

        if ($screenings['sex'] === 'Male'
            && '55' <= $screenings['age']
            && $screenings['age'] <= '69'
            && $screenings['prostate_cancer_screening'] === '10+ years ago/Never/Unsure') {
            $prostateCancer = $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Male'
                  && $screenings['race'] === 'African American/Black'
                  && $screenings['prostate_cancer_screening'] === '10+ years ago/Never/Unsure') {
            $prostateCancer = $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Male' && $prostateCancerSelected === true) {
            $prostateCancer = $this->getTaskRecommendations($title, $index);
        }

        return $prostateCancer;
    }

    public function colorectalCancer($patientPppData, $title)
    {
        $index            = 4;
        $colorectalCancer = [];

        $screenings['age']                         = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';
        $screenings['colorectal_cancer_screening'] = ! empty($patientPppData->answers_for_eval['colorectal_cancer_screening'])
            ? $patientPppData->answers_for_eval['colorectal_cancer_screening']
            : 'N/A';
        $screenings['family_conditions']           = ! empty($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';

        $colorectalCancerSelected = $this->checkForConditionSelected($screenings, $condition = 'Colorectal Cancer',
            $checkInCategory = 'family_conditions');

        if ('50' <= $screenings['age']
            && $screenings['age'] <= '75'
            || $colorectalCancerSelected === true
            || $screenings['colorectal_cancer_screening'] === 'In the last 6-10 years'
            || $screenings['colorectal_cancer_screening'] === 'Never/10 years ago') {

            $colorectalCancer = $this->getTaskRecommendations($title, $index);
        }

        return $colorectalCancer;
    }

    public function skinCancer($patientPppData, $title)
    {
        $index                            = 5;
        $skinCancer                       = [];
        $screenings['multipleQuestion16'] = ! empty($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $screenings['family_conditions'] = ! empty($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';

        $screenings['family_members_with_condition'] = ! empty($patientPppData->answers_for_eval['family_members_with_condition'])
            ? $patientPppData->answers_for_eval['family_members_with_condition']
            : 'N/A';

        $hasSkinCancerSelected = $this->checkForConditionSelected($screenings, $condition = 'Skin Cancer',
            $checkInCategory = 'family_conditions');

        $conditionWithTypeIsSelected = $this->checkForConditionWithType($screenings,
            'multipleQuestion16',
            'Cancer',
            'Skin');

        $famMembWithSelectCond = $this->countFamilyMembersWithCondition($screenings, $hasSkinCancerSelected);

        if ($hasSkinCancerSelected === true && $famMembWithSelectCond >= '2' || $conditionWithTypeIsSelected === true) {
            $skinCancer = $this->getTaskRecommendations($title, $index);
        }

        return $skinCancer;
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
    public function checkForConditionWithType(
        $screenings,
        string $checkInCategory,
        string $conditionName,
        string $conditionType
    ) {
        $conditionWithTypeIsSelected = [];
        $checkInAnswers              = $screenings;
        foreach ($checkInAnswers[$checkInCategory] as $data) {
            if (array_key_exists('type', $data)
                && isset($data['type'])) {
                $data['name'] === $conditionName && $data['type'] === $conditionType
                    ? $conditionWithTypeIsSelected = true
                    : $conditionWithTypeIsSelected = false;
            }
        }

        return $conditionWithTypeIsSelected;
    }

    /**
     * @param $screenings
     * @param bool $hasSkinCancerSelected
     *
     * @return int
     */
    public function countFamilyMembersWithCondition($screenings, bool $hasSkinCancerSelected): int
    {
        $answers         = [];
        $checkInCategory = 'family_members_with_condition';
        $checkInAnswers  = $screenings;
        foreach ($checkInAnswers[$checkInCategory] as $data) {
            if ($data['name'] === $hasSkinCancerSelected) {
                $answers = $data['family'];
            }
        }
        $famMembWithSelectCond = count($answers);

        return $famMembWithSelectCond;
    }

    public function osteoporosis($patientPppData, $title)
    {
        $index             = 0;
        $osteoporosis      = [];
        $screenings['sex'] = ! empty($patientPppData->answers_for_eval['sex'])
            ? $patientPppData->answers_for_eval['sex']
            : 'N/A';

        $screenings['age'] = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';

        $screenings['osteoporosis_screening'] = ! empty($patientPppData->answers_for_eval['osteoporosis_screening'])
            ? $patientPppData->answers_for_eval['osteoporosis_screening']
            : 'N/A';

        $screenings['fall_risk'] = ! empty($patientPppData->answers_for_eval['fall_risk'])
            ? $patientPppData->answers_for_eval['fall_risk']
            : 'N/A';

        if ($screenings['sex'] === 'Female'
            && $screenings['age'] >= '65'
            && $screenings['osteoporosis_screening'] !== 'In the last year') {
            $osteoporosis = $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['sex'] === 'Male'
                  && $screenings['age'] >= '70'
                  && $screenings['osteoporosis_screening'] !== 'In the last year') {
            $osteoporosis = $this->getTaskRecommendations($title, $index);
        } elseif ($screenings['fall_risk'] === 'Yes'
                  && $screenings['osteoporosis_screening'] !== 'In the last year') {
            $osteoporosis = $this->getTaskRecommendations($title, $index);
        }

        return $osteoporosis;
    }


    public function glaukoma($patientPppData, $title)
    {
        $index                            = 1;
        $glaukoma                         = [];
        $screenings['glaukoma_screening'] = ! empty($patientPppData->answers_for_eval['glaukoma_screening'])
            ? $patientPppData->answers_for_eval['glaukoma_screening']
            : 'N/A';
        if ($screenings['glaukoma_screening'] !== 'In the last year') {
            $glaukoma = $this->getTaskRecommendations($title, $index);
        }

        return $glaukoma;
    }

    public function diabetes($patientPppData, $title)
    {
        $index             = 2;
        $diabetes          = [];
        $screenings['age'] = ! empty($patientPppData->answers_for_eval['age'])
            ? $patientPppData->answers_for_eval['age']
            : 'N/A';
        $screenings['bmi'] = ! empty($patientPppData->answers_for_eval['bmi'])
            ? $patientPppData->answers_for_eval['bmi']
            : 'N/A';

        if ($screenings['age'] > '20' && $screenings['bmi'] >= '25') {
            $diabetes = $this->getTaskRecommendations($title, $index);
        }

        return $diabetes;
    }

    public function cholesterolDyslipidemia($patientPppData, $title)
    {
        $index                            = 3;
        $cholesterolDyslipidemia          = [];
        $screenings['blood_pressure']     = ! empty($patientPppData->answers_for_eval['blood_pressure'])
            ? $patientPppData->answers_for_eval['blood_pressure']
            : 'N/A';
        $screenings['bmi']                = ! empty($patientPppData->answers_for_eval['bmi'])
            ? $patientPppData->answers_for_eval['bmi']
            : 'N/A';
        $screenings['multipleQuestion16'] = ! empty($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';
        $screenings['current_smoker']     = ! empty($patientPppData->answers_for_eval['current_smoker'])
            ? $patientPppData->answers_for_eval['current_smoker']
            : 'N/A';
        $screenings['physical_activity']  = ! empty($patientPppData->answers_for_eval['physical_activity'])
            ? $patientPppData->answers_for_eval['physical_activity']
            : 'N/A';
        $screenings['fatty_fried_foods']  = ! empty($patientPppData->answers_for_eval['fatty_fried_foods'])
            ? $patientPppData->answers_for_eval['fatty_fried_foods']
            : 'N/A';

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

            $cholesterolDyslipidemia = $this->getTaskRecommendations($title, $index);
        }

        return $cholesterolDyslipidemia;
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
    ) {
        $highBloodPressure = [];
        $checkInAnswers    = $screenings;
        foreach ($checkInAnswers[$checkInCategory] as $data) {

            if ($data['first_metric'] >= $conditionFirstMetric
                && $data['second_metric'] >= $conditionSecondMetric) {
                $highBloodPressure = true;
            } else {
                $highBloodPressure = false;
            }
        }

        return $highBloodPressure;
    }

    public function noMedicalPowerOfAttorney($patientPppData, $title)
    {

        $index                    = 4;
        $noMedicalPowerOfAttorney = [];

        $screenings['medical_attonery'] = ! empty($patientPppData->answers_for_eval['medical_attonery'])
            ? $patientPppData->answers_for_eval['medical_attonery']
            : 'N/A';

        if ($screenings['medical_attonery'] === 'No') {
            $noMedicalPowerOfAttorney = $this->getTaskRecommendations($title, $index);
        }

        return $noMedicalPowerOfAttorney;
    }

    public function noLivingWillAdvanceDirective($patientPppData, $title)
    {
        $index                        = 5;
        $noLivingWillAdvanceDirective = [];

        $screenings['living_will'] = ! empty($patientPppData->answers_for_eval['living_will'])
            ? $patientPppData->answers_for_eval['living_will']
            : 'N/A';

        if ($screenings['living_will'] === 'No') {
            $noLivingWillAdvanceDirective = $this->getTaskRecommendations($title, $index);
        }

        return $noLivingWillAdvanceDirective;
    }

    public function weightBmiOverweight($patientPppData, $title)
    {
        $index               = 1;
        $weightBmiOverweight = [];
        $weightBmi['bmi']    = ! empty($patientPppData->answers_for_eval['bmi'])
            ? $patientPppData->answers_for_eval['bmi']
            : 'N/A';

        if ($weightBmi['bmi'] >= '25') {
            $weightBmiOverweight = $this->getTaskRecommendations($title, $index);
        }

        return $weightBmiOverweight;
    }
}