<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\TaskRecommendations;

class PersonalizedPreventionPlanPrepareData
{
    const ADL_ISSUES = 'adl_issues';
    const ADL_TITLE  = 'ADL';

    const ALCOHOL_DEPENDENT = 'alcohol_dependent';
    const ALCOHOL_TITLE     = 'Alcohol';
    const BMI_HIGH          = 'bmi_high';

    const BMI_LOW                = 'bmi_low';
    const CANDY_SUGARY_BEVERAGES = 'candy_sugary_beverages';
    const CERVICAL_CANCER_ELDER  = 'cervical_cancer_elder';
    const CERVICAL_CANCER_YOUNG  = 'cervical_cancer';
    const CHICKEN_POX            = 'chicken_pox';
    const CHOLESTEROL            = 'cholesterol';
    const COGNITIVE_TITLE        = 'Cognitive Impairment';
    const COLORECTAL_CANCER      = 'colorectal_cancer';
    const CURRENT_MALE_SMOKER    = 'current_male_smoker';

    const CURRENT_SMOKER    = 'current_smoker';
    const DIABETES          = 'diabetes';
    const DRUGS_TITLE       = 'Recreational Drug Use';
    const EMOTIONAL_HEALTH  = 'emotional_health';
    const EMOTIONAL_TITLE   = 'Emotional Health';
    const FALL_RISK         = 'fall_risk';
    const FALL_RISK_TITLE   = 'Fall Risk';
    const FATTY_FRIED_FOODS = 'fatty_fried_foods';
    //vaccines
    const FLU           = 'flu';
    const FORMER_SMOKER = 'former_smoker';
    //indexes
    const FRUIT_VEGGIES         = 'fruits_veggies';
    const GLAUCOMA              = 'glaucoma';
    const HEARING_IMPAIRMENT    = 'hearing_impairment';
    const HEARING_TITLE         = 'Hearing Impairment';
    const HEPATITIS_B           = 'hepatitis_b';
    const HERPES_ZOSTER         = 'herpes_zoster';
    const HPV                   = 'hpv';
    const LESS_THREE_WEEK_OLDER = 'less_three_week_older';

    const LESS_THREE_WEEK_YOUNG = 'less_three_week_young';
    //screenings
    const MAMMOGRAM                     = 'mammogram';
    const MILD_COGNITIVE_IMPAIRMENT     = 'mild_cognitive_impairment';
    const MMR                           = 'mmr';
    const MODERATE_COGNITIVE_IMPAIRMENT = 'moderate_cognitive_impairment';
    const NLWAD                         = 'nlwad';
    // Advanced Care Planning
    const NMPA                   = 'nmpa'; //titles
    const NUTRITION_TITLE        = 'Nutrition';
    const OSTEOPOROSIS           = 'osteoporosis';
    const OTHER_TITLE            = 'Other misc';
    const PHYSICAL_TITLE         = 'Physical Activity';
    const PNEUMONOCOCCAL_VACCINE = 'pneumococcal_vaccine';
    const PROSTATE_CANCER        = 'prostate_cancer';

    const RECREATIONAL_DRUGS = 'recreational_drugs';
    const REPRODUCTIVE_AGE   = 'reproductive_age';
    const SCREENINGS_TITLE   = 'Screenings';
    const SEXUAL_TITLE       = 'Sexual Practices';
    const SKIN_CANCER        = 'skin_cancer';
    const TETANUS            = 'tetanus';
    const TOBACCO_TITLE      = 'Tobacco / Smoking';

    const UNPROTECTED_SEX  = 'unprotected_sex';
    const VACCINES_TITLE   = 'Immunizations / Vaccines';
    const VITALS_TITLE     = 'Vitals';
    const WEIGHT_BMI_TITLE = 'Weight / BMI';
    const WHOLE_GRAIN      = 'whole_grain';

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function adlWithNoHelp($patientPppData, $title, $index)
    {
        $adl['adl']                            = collect($patientPppData->answers_for_eval['adl'])->flatten()->toArray();
        $adl['assistance_in_daily_activities'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'assistance_in_daily_activities'
        );

        if ( ! empty($adl['adl'])
             && 'Yes' !== $adl['assistance_in_daily_activities']) {
            $getAdlRecommendations = $this->getTaskRecommendations($title, $index);
            $textToReplace         = '{insert all selected tasks in Q26}'; //todo:rename this in seeder and DB
            $adlAnswers            = $adl['adl'];
            $answersUnCapitalized  = collect($adlAnswers)->map(function ($answer) {
                return lcfirst($answer);
            })->toArray();

            $replacementText          = implode(', ', $answersUnCapitalized);
            $adlRecommendationBody    = $getAdlRecommendations['task_body'];
            $newAdlRecommendationBody = str_replace($textToReplace, $replacementText, $adlRecommendationBody);

            return [
                'task_body'           => $newAdlRecommendationBody,
                'report_table_data'   => $getAdlRecommendations['report_table_data'],
                'qualitative_trigger' => $getAdlRecommendations['qualitative_trigger'],
                'recommendation_body' => $getAdlRecommendations['recommendation_body'],
            ];
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function alcoholUse($patientPppData, $title, $index)
    {
        $alcoholData['alcohol_use'] = $this->getStringValue($patientPppData->answers_for_eval, 'alcohol_use');
        $alcoholData['sex']         = $this->getStringValue($patientPppData->answers_for_eval, 'sex');

        if (('Male' === $alcoholData['sex'] && '14+ drinks per week' === $alcoholData['alcohol_use'])
            || ('Female' === $alcoholData['sex'] && '7-10 drinks per week' === $alcoholData['alcohol_use'])
            || '10-14 drinks per week' === $alcoholData['alcohol_use']
            || '14+ drinks per week' === $alcoholData['alcohol_use']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function breastCancerMammogram($patientPppData, $title, $index)
    {
        $screenings['breast_cancer_screening'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['breast_cancer_screening'])
            ? $patientPppData->answers_for_eval['breast_cancer_screening']
            : 'N/A';
        $screenings['sex']               = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $screenings['age']               = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['family_conditions'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';
        $breastCancerSelected = $this->checkForConditionSelected(
            $screenings,
            $condition = 'Breast Cancer',
            $checkInCategory = 'family_conditions'
        );
        //@todo:also add if other or trans waiting answer from raph
        if ('Male' !== $screenings['sex'] && '50' < $screenings['age'] && $screenings['age'] < '74') {
            return $this->getTaskRecommendations($title, $index);
        }
        if ( ! ('In the last 2-3 years' === $screenings['breast_cancer_screening']
                      || 'In the last year' === $screenings['breast_cancer_screening'])
                   && 'Male' !== $screenings['sex']
                   && true === $breastCancerSelected) {
            return $this->getTaskRecommendations($title, $index);
        }
        if ('In the last year' !== $screenings['breast_cancer_screening']
                  && 'Male' !== $screenings['sex']
                  && true === $breastCancerSelected) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function candySugaryBeverages($patientPppData, $title, $index)
    {
        $nutritionData['candy_sugary_beverages'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'candy_sugary_beverages'
        );

        if ('0' !== $nutritionData['candy_sugary_beverages']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function cervicalCancerElder($patientPppData, $title, $index)
    {
        $screenings['sex']                       = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $screenings['age']                       = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['cervical_cancer_screening'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['cervical_cancer_screening'])
            ? $patientPppData->answers_for_eval['cervical_cancer_screening']
            : 'N/A';

        if ('Female' === $screenings['sex']
            && '30' <= $screenings['age']
            && $screenings['age'] <= '65'
            && 'In the last 6-10 years' === $screenings['cervical_cancer_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }
        if ('Female' === $screenings['sex']
                  && '30' <= $screenings['age']
                  && $screenings['age'] <= '65'
                  && '10+ years ago/Never/Unsure' === $screenings['cervical_cancer_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function cervicalCancerYoung($patientPppData, $title, $index)
    {
        $screenings['sex']                       = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $screenings['age']                       = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['cervical_cancer_screening'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['cervical_cancer_screening'])
            ? $patientPppData->answers_for_eval['cervical_cancer_screening']
            : 'N/A';

        if ('Male' !== $screenings['sex']
            && '21' <= $screenings['age']
            && $screenings['age'] <= '29'
            && 'In the last 2-3 years' !== $screenings['cervical_cancer_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }
        if ('Male' !== $screenings['sex']
                  && '21' <= $screenings['age']
                  && $screenings['age'] <= '29'
                  && 'In the last year' !== $screenings['cervical_cancer_screening']) {
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
     */
    public function checkForConditionSelected($screenings, $condition, $checkInCategory): bool
    {
        $answers        = [];
        $checkInAnswers = $screenings;
        if (is_array($checkInAnswers[$checkInCategory])) {
            $arr = $checkInAnswers[$checkInCategory];
            foreach ($arr as $data) {
                $answers[] = $data['name'];
            }
        }

        return in_array($condition, $answers);
    }

    /**
     * @param $screenings
     *
     * @return array|bool
     */
    public function checkForHighBloodPressure(
        $screenings,
        string $checkInCategory,
        string $conditionFirstMetric,
        string $conditionSecondMetric
    ) {
        $checkInAnswers = $screenings;
        $data           = $checkInAnswers[$checkInCategory];
        if ($data['first_metric'] >= $conditionFirstMetric
            && $data['second_metric'] >= $conditionSecondMetric) {
            return true;
        }

        return false;
    }

    /**
     * @param $screenings
     *
     *
     * @return array|bool
     */
    public function checkSkinCancerIsSelectedInQ16(
        $screenings,
        string $checkInCategory,
        string $conditionName,
        string $conditionType
    ) {
        $checkInAnswers = $screenings;
        if (is_array($checkInAnswers[$checkInCategory])) {
            $arr = $checkInAnswers[$checkInCategory];
            foreach ($arr as $data) {
                if (array_key_exists('type', $data)
                    && isset($data['type'])
                    && array_key_exists('name', $data)
                    && isset($data['name'])
                    && $data['name'] === $conditionName) {
                    return ProviderReportService::caseInsensitiveComparison($data['type'], $conditionType);
                }
            }
        }

        return false;
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function chickenPoxVaricella($patientPppData, $title, $index)
    {
        $vaccines['chicken_pox'] = $this->getStringValue($patientPppData->answers_for_eval, 'chicken_pox');
        if ('No' === $vaccines['chicken_pox']
            || 'Unsure' === $vaccines['chicken_pox']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function cholesterolDyslipidemia($patientPppData, $title, $index)
    {
        $screenings['blood_pressure'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['blood_pressure'])
            ? $patientPppData->answers_for_eval['blood_pressure']
            : 'N/A';
        $screenings['bmi']                = $this->getStringValue($patientPppData->answers_for_eval, 'bmi');
        $screenings['multipleQuestion16'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';
        $screenings['current_smoker']    = $this->getStringValue($patientPppData->answers_for_eval, 'current_smoker');
        $screenings['physical_activity'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'physical_activity'
        );
        $screenings['fatty_fried_foods'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'fatty_fried_foods'
        );

        $diabetesSelected = $this->checkForConditionSelected(
            $screenings,
            'Diabetes',
            'multipleQuestion16'
        );

        $highBloodPressure = $this->checkForHighBloodPressure(
            $screenings,
            'blood_pressure',
            '130',
            '80'
        );

        if ('true' === $highBloodPressure
            || $screenings['bmi'] >= '30'
            || true === $diabetesSelected
            || 'Yes' === $screenings['current_smoker']
            || '<3 times a week' === $screenings['physical_activity']
            || 'Never' === $screenings['physical_activity']
            || '0' !== $screenings['fatty_fried_foods']
            || '1' !== $screenings['fatty_fried_foods']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function colorectalCancer($patientPppData, $title, $index)
    {
        $screenings['age']                         = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['colorectal_cancer_screening'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['colorectal_cancer_screening'])
            ? $patientPppData->answers_for_eval['colorectal_cancer_screening']
            : 'N/A';
        $screenings['family_conditions'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';

        $colorectalCancerSelected = $this->checkForConditionSelected(
            $screenings,
            $condition = 'Colorectal Cancer',
            $checkInCategory = 'family_conditions'
        );

        if (('50' <= $screenings['age'] && $screenings['age'] <= '75')
            || true === $colorectalCancerSelected
            || 'In the last 6-10 years' === $screenings['colorectal_cancer_screening']
            || 'Never/10 years ago' === $screenings['colorectal_cancer_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $screenings
     * @param string $condition
     */
    public function countFamilyMembersWithSkinCancer($screenings, $condition): int
    {
        $answers         = [];
        $checkInCategory = 'family_members_with_condition';
        $checkInAnswers  = $screenings;
        if (is_array($checkInAnswers[$checkInCategory])) {
            foreach ($checkInAnswers[$checkInCategory] as $data) {
                if ($data['name'] === $condition) {
                    $answers = $data['family'];
                }
            }
        }

        return count($answers);
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function currentSmoker($patientPppData, $title, $index)
    {
        $smokingData['current_smoker'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'current_smoker'
        );
        $smokingData['smoker_interested_quitting'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'smoker_interested_quitting'
        );

        if ('Yes' === $smokingData['current_smoker'] && 'I already quit' !== $smokingData['smoker_interested_quitting']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function currentSmokerMale($patientPppData, $title, $index)
    {
        $smokingData['current_smoker'] = $this->getStringValue($patientPppData->answers_for_eval, 'current_smoker');
        $smokingData['age']            = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $smokingData['sex']            = $this->getStringValue($patientPppData->answers_for_eval, 'sex');

        if ('Yes' === $smokingData['current_smoker'] && 'Male' === $smokingData['sex'] && $smokingData['age'] <= '75' && $smokingData['age'] >= '65') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @throws \Exception
     * @return array|string
     */
    public function depression($patientPppData, $title, $index)
    {
        $emotional['emotional_little_interest'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'emotional_little_interest'
        );
        $emotional['emotional_depressed'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'emotional_depressed'
        );
        $depressionScoresArray = ProviderReportService::depressionScoreArray();

        $depressionScore = $this->getDepressionScore($depressionScoresArray, $emotional);

        return $depressionScore >= 5
            ? $this->getTaskRecommendations($title, $index)
            : [];
    }

    public function diabetes($patientPppData, $title, $index)
    {
        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['bmi'] = $this->getStringValue($patientPppData->answers_for_eval, 'bmi');

        if ($screenings['age'] > '20' && $screenings['bmi'] >= '25') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function fattyFriedFoods($patientPppData, $title, $index)
    {
        $nutritionData['fatty_fried_foods'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'fatty_fried_foods'
        );

        if ('3' === $nutritionData['fatty_fried_foods'] || '4+' === $nutritionData['fatty_fried_foods']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function fluInfluenza($patientPppData, $title, $index)
    {
        $vaccines['flu_influenza'] = $this->getStringValue($patientPppData->answers_for_eval, 'flu_influenza');
        if ('No' === $vaccines['flu_influenza']
            //unsure does not in exist in answers options (HRA Q26.). Im waiting for Raph's feedback on this.
            || 'Unsure' === $vaccines['flu_influenza']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function formerSmoker($patientPppData, $title, $index)
    {
        $smokingData['current_smoker'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'current_smoker'
        );
        $smokingData['smoker_interested_quitting'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'smoker_interested_quitting'
        );

        if ('Yes' === $smokingData['current_smoker'] && 'I already quit' === $smokingData['smoker_interested_quitting']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array
     */
    public function fruitVeggies($patientPppData, $title, $index)
    {
        $nutritionData['fruit_veggies'] = $this->getStringValue($patientPppData->answers_for_eval, 'fruit_veggies');
        if ('4+' !== $nutritionData['fruit_veggies']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $depressionScoresArray
     * @param $emotional
     *
     * @throws \Exception
     * @return mixed
     */
    public function getDepressionScore($depressionScoresArray, $emotional)
    {
        $littleInterestScore = $depressionScoresArray[strtolower(ProviderReportService::checkInputValueIsNotEmpty(
            $emotional['emotional_little_interest'],
            '22.1',
            []
        ))];
        $depressionScore = $depressionScoresArray[strtolower(ProviderReportService::checkInputValueIsNotEmpty(
            $emotional['emotional_depressed'],
            '22.2',
            []
        ))];

        return $littleInterestScore + $depressionScore;
    }

    /**
     * Return ordered checkList Data.
     *
     * @param $personalizedHealthAdvices
     *
     * @return array
     */
    public static function getOrderedSuggestedChecklist($personalizedHealthAdvices)
    {
        $suggestedChecklistData = collect();
        foreach ($personalizedHealthAdvices as $advice => $tasks) {
            $suggestedChecklistData[] = $tasks['table_data'];
        }

        return $suggestedChecklistData->sortBy(function ($collections) {
            foreach ($collections as $collection => $arrays) {
                return $arrays[0]['emphasize_code'];
            }
        }, SORT_REGULAR, true)->values()->all();
    }

    /**
     * @param $title
     * @param $index
     *
     * @return string
     */
    public function getTaskRecommendations($title, $index)
    {
        $taskRecommendation = TaskRecommendations::where('title', '=', $title)->first();

        return isset($taskRecommendation->data[$index])
            ? $taskRecommendation->data[$index]
            : '';
    }

    public function glaukoma($patientPppData, $title, $index)
    {
        $screenings['glaukoma_screening'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'glaukoma_screening'
        );
        if ('In the last year' !== $screenings['glaukoma_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function hearingImpairment($patientPppData, $title, $index)
    {
        $hearingImpairment['hearing_impairment'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'hearing_impairment'
        );

        if ('Yes' === $hearingImpairment['hearing_impairment']
            || 'Sometimes' === $hearingImpairment['hearing_impairment']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function hepatitisB($patientPppData, $title, $index)
    {
        $vaccines['hepatitis_b'] = $this->getStringValue($patientPppData->answers_for_eval, 'hepatitis_b');
        if ('No' === $vaccines['hepatitis_b']
            || 'Unsure' === $vaccines['hepatitis_b']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function humanPapillomavirus($patientPppData, $title, $index)
    {
        $vaccines['human_papillomavirus'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'human_papillomavirus'
        );
        $vaccines['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        if ($vaccines['age'] <= '26'
            && ('No' === $vaccines['human_papillomavirus'] || 'Unsure' === $vaccines['human_papillomavirus'])) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function measlesMumpsRubella($patientPppData, $title, $index)
    {
        $vaccines['rubella'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'rubella'
        );
        if ('No' === $vaccines['rubella']
            || 'Unsure' === $vaccines['rubella']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function mildCognitiveImpairment($patientPppData, $title, $index)
    {
        $cognitiveAssessment['cognitive_assessment'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'cognitive_assessment'
        );

        if ('3' === $cognitiveAssessment['cognitive_assessment']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function modToSevNeurocognitiveImpairment($patientPppData, $title, $index)
    {
        $cognitiveAssessment['cognitive_assessment'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'cognitive_assessment'
        );

        if ('3' !== $cognitiveAssessment['cognitive_assessment'] && '4' !== $cognitiveAssessment['cognitive_assessment'] && '5' !== $cognitiveAssessment['cognitive_assessment']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function noLivingWillAdvanceDirective($patientPppData, $title, $index)
    {
        $screenings['living_will'] = $this->getStringValue($patientPppData->answers_for_eval, 'living_will');

        if ('No' === $screenings['living_will']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function noMedicalPowerOfAttorney($patientPppData, $title, $index)
    {
        $screenings['medical_attorney'] = $this->getStringValue($patientPppData->answers_for_eval, 'medical_attorney');

        if ('No' === $screenings['medical_attorney']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    public function osteoporosis($patientPppData, $title, $index)
    {
        $screenings['sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'sex');

        $screenings['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        $screenings['osteoporosis_screening'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'osteoporosis_screening'
        );

        $screenings['fall_risk'] = $this->getStringValue($patientPppData->answers_for_eval, 'fall_risk');

        if ('Female' === $screenings['sex']
            && $screenings['age'] >= '65'
            && 'In the last year' !== $screenings['osteoporosis_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }
        if ('Male' === $screenings['sex']
                  && $screenings['age'] >= '70'
                  && 'In the last year' !== $screenings['osteoporosis_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }
        if ('Yes' === $screenings['fall_risk']
                  && 'In the last year' !== $screenings['osteoporosis_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function patientHasFallen($patientPppData, $title, $index)
    {
        $fallRisk['fall_risk'] = $this->getStringValue($patientPppData->answers_for_eval, 'fall_risk');

        return 'No' !== $fallRisk['fall_risk']
            ? $this->getTaskRecommendations($title, $index)
            : [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function physicalActivityOlder($patientPppData, $title, $index)
    {
        $physicalActivity['physical_activity'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'physical_activity'
        );

        $physicalActivity['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        if ($physicalActivity['age'] >= '65' && ('Never' === $physicalActivity['physical_activity']
                                                 || '<3 times a week' === $physicalActivity['physical_activity'])) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function physicalActivityYounger($patientPppData, $title, $index)
    {
        $physicalActivity['physical_activity'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'physical_activity'
        );

        $physicalActivity['age'] = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        if ($physicalActivity['age'] < '65' && ('Never' === $physicalActivity['physical_activity']
                                                || '<3 times a week' === $physicalActivity['physical_activity'])) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function pneumococcalVaccine($patientPppData, $title, $index)
    {
        $vaccines['pneumococcal_vaccine'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'pneumococcal_vaccine'
        );
        if ('No' === $vaccines['pneumococcal_vaccine']
            || 'Unsure' === $vaccines['pneumococcal_vaccine']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /*
     * https://docs.google.com/document/d/1ZC68KlBgKFYZIDd9uTVBAw_P85oO1VjloHw9vZt2Tmc/edit
    each evaluation from this doc belongs to a function
    */

    /**
     * @param $patientPppData
     *
     * @throws \Exception
     * @return \Illuminate\Support\Collection
     */
    public function prepareRecommendations($patientPppData)
    {
        $nutritionRecommendations = [
            $title = self::NUTRITION_TITLE,
            $image = 'carrot',
            $fruitVeggies = $this->fruitVeggies($patientPppData, $title, self::FRUIT_VEGGIES),
            $wholeGrain = $this->wholeGrain($patientPppData, $title, self::WHOLE_GRAIN),
            $fattyFriedFoods = $this->fattyFriedFoods($patientPppData, $title, self::FATTY_FRIED_FOODS),
            $candySugaryBeverages = $this->candySugaryBeverages($patientPppData, $title, self::CANDY_SUGARY_BEVERAGES),
        ];

        $smokingRecommendations = [
            $title = self::TOBACCO_TITLE,
            $image = 'cigarette',
            $currentSmoker = $this->currentSmoker($patientPppData, $title, self::CURRENT_SMOKER),
            $currentSmokerAge = $this->currentSmokerMale($patientPppData, $title, self::CURRENT_MALE_SMOKER),
            $formerSmoker = $this->formerSmoker($patientPppData, $title, self::FORMER_SMOKER),
        ];

        $alcoholRecommendations = [
            $title = self::ALCOHOL_TITLE,
            $image = 'wine',
            $alcoholUse = $this->alcoholUse($patientPppData, $title, self::ALCOHOL_DEPENDENT),
        ];

        $recreationalDrugsRecommendations = [
            $title = self::DRUGS_TITLE,
            $image = 'flower-3',
            $recreationalDrugs = $this->recreationalDrugs($patientPppData, $title, self::RECREATIONAL_DRUGS),
        ];

        $physicalActivity = [
            $title = self::PHYSICAL_TITLE,
            $image = 'dumbell',
            $physicalActivityYounger = $this->physicalActivityYounger(
                $patientPppData,
                $title,
                self::LESS_THREE_WEEK_YOUNG
            ),
            $physicalActivityOlder = $this->physicalActivityOlder($patientPppData, $title, self::LESS_THREE_WEEK_OLDER),
        ];

        $weightBmi = [
            $title = self::WEIGHT_BMI_TITLE,
            $image = 'weight-scale',
            $weightBmiUnderweight = $this->weightBmiUnderweight($patientPppData, $title, self::BMI_LOW),
            $weightBmiOverweight = $this->weightBmiOverweight($patientPppData, $title, self::BMI_HIGH),
        ];

        $sexualPractices = [
            $title = self::SEXUAL_TITLE,
            $image = 'hearts',
            $unprotectedSex = $this->unprotectedSex($patientPppData, $title, self::UNPROTECTED_SEX),
            $womanOfReproductiveAge = $this->womanOfReproductiveAge($patientPppData, $title, self::REPRODUCTIVE_AGE),
        ];

        $emotionalHealth = [
            $title = self::EMOTIONAL_TITLE,
            $image = 'happy-face',
            $depression = $this->depression($patientPppData, $title, self::EMOTIONAL_HEALTH),
        ];

        $fallRisk = [
            $title = self::FALL_RISK_TITLE,
            $image = 'patch',
            $patientHasFallen = $this->patientHasFallen($patientPppData, $title, self::FALL_RISK),
        ];

        $hearingImpairment = [
            $title = self::HEARING_TITLE,
            $image = 'volume-half',
            $patientHasHearingImper = $this->hearingImpairment($patientPppData, $title, self::HEARING_IMPAIRMENT),
        ];

        $cognitiveImpairment = [
            $title = self::COGNITIVE_TITLE,
            $image = 'thought-bubble',
            $mildCognitiveImpairment = $this->mildCognitiveImpairment(
                $patientPppData,
                $title,
                self::MILD_COGNITIVE_IMPAIRMENT
            ),
            $modToSevNeurocognitiveImpairment = $this->modToSevNeurocognitiveImpairment(
                $patientPppData,
                $title,
                self::MODERATE_COGNITIVE_IMPAIRMENT
            ),
        ];

        $adl = [
            $title = self::ADL_TITLE,
            $image = 'raised-hand',
            $adlWithNoHelp = $this->adlWithNoHelp($patientPppData, $title, self::ADL_ISSUES),
        ];

        $immunizationsVaccines = [
            $title = self::VACCINES_TITLE,
            $image = 'syringe',
            $fluInfluenza = $this->fluInfluenza($patientPppData, $title, self::FLU),
            $tetanusDiphtheria = $this->tetanusDiphtheria($patientPppData, $title, self::TETANUS),
            $chickenPox = $this->chickenPoxVaricella($patientPppData, $title, self::CHICKEN_POX),
            $hepatitisB = $this->hepatitisB($patientPppData, $title, self::HEPATITIS_B),
            $measlesMumpsRubella = $this->measlesMumpsRubella($patientPppData, $title, self::MMR),
            $humanPapillomavirus = $this->humanPapillomavirus($patientPppData, $title, self::HPV),
            $shingles = $this->shingles($patientPppData, $title, self::HERPES_ZOSTER),
            $pneumococcalVaccine = $this->pneumococcalVaccine($patientPppData, $title, self::PNEUMONOCOCCAL_VACCINE),
        ];

        $screenings = [
            $title = self::SCREENINGS_TITLE,
            $image = 'clipboard-list',
            $breastCancerMammogram = $this->breastCancerMammogram($patientPppData, $title, self::MAMMOGRAM),
            $cervicalCancerYoung = $this->cervicalCancerYoung($patientPppData, $title, self::CERVICAL_CANCER_YOUNG),
            $cervicalCancerElder = $this->cervicalCancerElder($patientPppData, $title, self::CERVICAL_CANCER_ELDER),
            $prostateCancer = $this->prostateCancer($patientPppData, $title, self::PROSTATE_CANCER),
            $colorectalCancer = $this->colorectalCancer($patientPppData, $title, self::COLORECTAL_CANCER),
            $skinCancer = $this->skinCancer($patientPppData, $title, self::SKIN_CANCER),
            $osteoporosis = $this->osteoporosis($patientPppData, $title, self::OSTEOPOROSIS),
            $glaukoma = $this->glaukoma($patientPppData, $title, self::GLAUCOMA),
            $diabetes = $this->diabetes($patientPppData, $title, self::DIABETES),
            $cholesterolDyslipidemia = $this->cholesterolDyslipidemia($patientPppData, $title, self::CHOLESTEROL),
        ];

        $otherMisc = [
            $title = self::OTHER_TITLE,
            $image = 'layout-4-blocks',
            $noMedicalPowerOfAttorney = $this->noMedicalPowerOfAttorney($patientPppData, $title, self::NMPA),
            $livingWill = $this->noLivingWillAdvanceDirective($patientPppData, $title, self::NLWAD),
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

        $personalizedHealthAdvices = $recommendationTasks
            ->map(function ($recommendation) {
                $tasks = array_slice($recommendation, 2);
                $tableData = [];
                foreach ($tasks as $task) {
                    if ( ! array_key_exists('report_table_data', $task)) {
                        continue;
                    }

                    $tableData[] = collect($task['report_table_data'])->transform(function ($table) {
                        $codeWithText = '99498 (if same day as AWV, bill w/ mod. 33 on same claim and Dr. as AWV)';
                        $emphasisedBody = '(NOTE: $0 co-pay if done during AWV)';

                        $emphasizeCode = false;
                        $body = $table['body'];
                        $code = $table['code'];

                        if (false !== strpos($table['code'], $codeWithText)) {
                            $emphasizeCode = true;
                            $body = (str_ireplace(
                                $emphasisedBody,
                                "<class style='background-color: #cfe7f3;'>{$emphasisedBody}</class>",
                                $table['body']
                            ));
                            $code = (str_ireplace(
                                $codeWithText,
                                "<class style='background-color: #cfe7f3;'>{$codeWithText}</class>",
                                $table['code']
                            ));
                        }

                        return [
                            'body'           => $body,
                            'code'           => $code,
                            'time_frame'     => $table['time_frame'],
                            'emphasize_code' => $emphasizeCode,
                        ];
                    });
                }

                return [
                    'title'      => $recommendation[0],
                    'image'      => $recommendation[1],
                    'tasks'      => $tasks,
                    'table_data' => $tableData, // i can't order them here cause they re not all collected yet.
                ];
            })
            ->filter(function ($item) {
                return ! empty($item['tasks']);
            });

        return $personalizedHealthAdvices;
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function prostateCancer($patientPppData, $title, $index)
    {
        $screenings['race']                      = $this->getStringValue($patientPppData->answers_for_eval, 'race');
        $screenings['sex']                       = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $screenings['age']                       = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $screenings['prostate_cancer_screening'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['prostate_cancer_screening'])
            ? $patientPppData->answers_for_eval['prostate_cancer_screening']
            : 'N/A';
        $screenings['multipleQuestion16'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $prostateCancerSelected = $this->checkForConditionSelected(
            $screenings,
            $condition = 'Prostate Cancer',
            $checkInCategory = 'multipleQuestion16'
        );

        if ('Female' !== $screenings['sex']
            && '55' <= $screenings['age']
            && $screenings['age'] <= '69'
            && '10+ years ago/Never/Unsure' === $screenings['prostate_cancer_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }
        if ('Female' !== $screenings['sex']
                  && 'Black/African-Ameri.' === $screenings['race']
                  && '10+ years ago/Never/Unsure' === $screenings['prostate_cancer_screening']) {
            return $this->getTaskRecommendations($title, $index);
        }
        if ('Female' !== $screenings['sex'] && true === $prostateCancerSelected) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function recreationalDrugs($patientPppData, $title, $index)
    {
        $recreationalDrugs['recreational_drugs'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'recreational_drugs'
        );

        if ('Yes' === $recreationalDrugs['recreational_drugs']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function shingles($patientPppData, $title, $index)
    {
        $vaccines['shingles'] = $this->getStringValue($patientPppData->answers_for_eval, 'shingles');
        $vaccines['age']      = $this->getStringValue($patientPppData->answers_for_eval, 'age');

        if ($vaccines['age'] > '50' && ('No' === $vaccines['shingles'] || 'Unsure' === $vaccines['shingles'])) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function skinCancer($patientPppData, $title, $index)
    {
        $screenings['multipleQuestion16'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $screenings['family_conditions'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['family_conditions'])
            ? $patientPppData->answers_for_eval['family_conditions']
            : 'N/A';

        $screenings['family_members_with_condition'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['family_members_with_condition'])
            ? $patientPppData->answers_for_eval['family_members_with_condition']
            : 'N/A';

        $hasSkinCancerSelectedInQ18 = $this->checkForConditionSelected(
            $screenings,
            $condition = 'Skin Cancer',
            $checkInCategory = 'family_conditions'
        );

        $skinCancerIsSelectedInQ16 = $this->checkSkinCancerIsSelectedInQ16(
            $screenings,
            'multipleQuestion16',
            'Cancer',
            'skin'
        );

        $countFamilyMembersWithSkinCancerFromQ18 = $this->countFamilyMembersWithSkinCancer(
            $screenings,
            $condition = 'Skin Cancer'
        );

        if ((true === $hasSkinCancerSelectedInQ18 && $countFamilyMembersWithSkinCancerFromQ18 >= '2') || true === $skinCancerIsSelectedInQ16) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function tetanusDiphtheria($patientPppData, $title, $index)
    {
        $vaccines['tetanus_diphtheria'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'tetanus_diphtheria'
        );
        $vaccines['rubella'] = $this->getStringValue($patientPppData->answers_for_eval, 'rubella');
        if ('No' === $vaccines['tetanus_diphtheria']
            || 'Unsure' === $vaccines['tetanus_diphtheria']
            || 'No' === $vaccines['rubella']
            || 'Unsure' === $vaccines['rubella']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function unprotectedSex($patientPppData, $title, $index)
    {
        $sexualLife['sexually_active']   = $this->getStringValue($patientPppData->answers_for_eval, 'sexually_active');
        $sexualLife['multiple_partners'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'multiple_partners'
        );
        $sexualLife['safe_sex'] = $this->getStringValue($patientPppData->answers_for_eval, 'safe_sex');

        if ('Yes' === $sexualLife['sexually_active']
            && 'Yes' === $sexualLife['multiple_partners']
            && 'Never' === $sexualLife['safe_sex']) {
            return $this->getTaskRecommendations($title, $index);
        }
        if ('Yes' === $sexualLife['sexually_active']
                  && 'Yes' === $sexualLife['multiple_partners']
                  && 'Sometimes' === $sexualLife['safe_sex']) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function weightBmiOverweight($patientPppData, $title, $index)
    {
        $weightBmi['bmi'] = $this->getStringValue($patientPppData->answers_for_eval, 'bmi');

        if ($weightBmi['bmi'] >= '25') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function weightBmiUnderweight($patientPppData, $title, $index)
    {
        $weightBmi['bmi'] = $this->getStringValue($patientPppData->answers_for_eval, 'bmi');

        if ($weightBmi['bmi'] <= '13.5') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function wholeGrain($patientPppData, $title, $index)
    {
        $nutritionData['whole_grain']        = $this->getStringValue($patientPppData->answers_for_eval, 'whole_grain');
        $nutritionData['multipleQuestion16'] = ! ProviderReportService::filterAnswer($patientPppData->answers_for_eval['multipleQuestion16'])
            ? $patientPppData->answers_for_eval['multipleQuestion16']
            : 'N/A';

        $diabetesSelected = $this->checkForConditionSelected(
            $nutritionData,
            $condition = 'Diabetes',
            $checkInCategory = 'multipleQuestion16'
        );

        if (['3-4' !== $nutritionData['whole_grain'] || '5+' !== $nutritionData['whole_grain']]
            && true !== $diabetesSelected) {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $patientPppData
     * @param $title
     * @param $index
     *
     * @return array|string
     */
    public function womanOfReproductiveAge($patientPppData, $title, $index)
    {
        $sexualLife['sex']                      = $this->getStringValue($patientPppData->answers_for_eval, 'sex');
        $sexualLife['age']                      = $this->getStringValue($patientPppData->answers_for_eval, 'age');
        $sexualLife['domestic_violence_screen'] = $this->getStringValue(
            $patientPppData->answers_for_eval,
            'domestic_violence_screen'
        );

        if ('Female' === $sexualLife['sex']
            && '10+ years ago/Never/Unsure' === $sexualLife['domestic_violence_screen']
            && '15' <= $sexualLife['age']
            && $sexualLife['age'] <= '44') {
            return $this->getTaskRecommendations($title, $index);
        }

        return [];
    }

    /**
     * @param $coll
     * @param $key
     * @param string $default
     *
     * @return string
     */
    private function getStringValue($coll, $key, $default = 'N/A')
    {
        if ( ! $coll || ProviderReportService::filterAnswer($coll)) {
            return $default;
        }

        if ( ! isset($coll[$key])) {
            return $default;
        }

        return getStringValueFromAnswer($coll[$key], $default);
    }
}
