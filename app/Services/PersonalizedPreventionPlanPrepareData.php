<?php

namespace App\Services;


use App\TaskRecommendations;

class PersonalizedPreventionPlanPrepareData
{
    public function prepareRecommendations($patientPppData)
    {
        $nutritionRecommendations = [
            'title' => $title = 'Nutrition',
            $fruitVeggies = $this->evaluateAnswer6($patientPppData, $title),
            $wholeGrain = $this->evaluateAnswer7($patientPppData, $title),
            $fattyFriedFoods = $this->evaluateAnswer8($patientPppData, $title),
            $candySugaryBeverages = $this->evaluateAnswer9($patientPppData, $title),
        ];
        $smokingRecommendations   = [
            'title' => $title = 'Tobacco/Smoking',
            $currentSmoker = $this->evaluateAnswer11($patientPppData, $title),
            $currentSmokerAge = $this->evaluateAnswer2_4_11($patientPppData, $title),
            $formerSmoker = $this->formerSmoker($patientPppData, $title),
        ];

        return collect([
            'recommendation_tasks' => [
                'nutrition_recommendations'       => $nutritionRecommendations,
                'tobacco_smoking_recommendations' => $smokingRecommendations,
            ],
        ]);
    }

    public function evaluateAnswer6($patientPppData, $title)
    {
        $index                          = 0;
        $fruitVeggies         = [];
        $nutritionData['fruit_veggies'] = ! empty($patientPppData->answers_for_eval['fruit_veggies'])
            ? $patientPppData->answers_for_eval['fruit_veggies']
            : 'N/A';
        if ($nutritionData['fruit_veggies'] !== '+4') {
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

        /*$nutritionRec = [];
        foreach ($recommendation as $data) {
            if ($data['sub_title'] === $subTitle) {

                $nutritionRec = collect([
                    'task_body'           => $data['task_body'],
                    'sub_title'           => $data['sub_title'],
                    'recommendation_body' => $data['recommendation_body'],

                ]);
            };
        }*/

        return $nutritionRec;

    }

    public function evaluateAnswer7($patientPppData, $title)
    {

        $index                        = 1;
        $wholeGrain       = [];
        $nutritionData['whole_grain'] = ! empty($patientPppData->answers_for_eval['whole_grain'])
            ? $patientPppData->answers_for_eval['whole_grain']
            : 'N/A';

        $nutritionData['diabetes'] = ! empty($patientPppData->answers_for_eval['diabetes'])
            ? $patientPppData->answers_for_eval['diabetes']
            : 'N/A';
//@todo:should extract this later
        $diabetesSelected = [];
        $answers          = [];
        $checkForDiabetes = $nutritionData;
        foreach ($checkForDiabetes['diabetes'] as $data) {
            $answers[] = $data['name'];
        }

        if (in_array('Diabetes', $answers)) {
            $diabetesSelected = true;
        }

        if ($nutritionData['whole_grain'] !== '3-4' || $nutritionData['whole_grain'] !== '5+' && $diabetesSelected !== true) {
            $wholeGrain = $this->getTaskRecommendations($title, $index);
        }

        return $wholeGrain;
    }

    public function evaluateAnswer8($patientPppData, $title)
    {
        $index                  = 2;
        $fattyFriedFoods = [];

        $nutritionData['fatty_fried_foods'] = ! empty($patientPppData->answers_for_eval['fatty_fried_foods'])
            ? $patientPppData->answers_for_eval['fatty_fried_foods']
            : 'N/A';
        if ($nutritionData['fatty_fried_foods'] === '3' || $nutritionData['fatty_fried_foods'] === '4+') {
            $fattyFriedFoods = $this->getTaskRecommendations($title, $index);
        }

        return $fattyFriedFoods;
    }

    public function evaluateAnswer9($patientPppData, $title)
    {
        $index                                   = 3;
        $candySugaryBeverages                  = [];
        $nutritionData['candy_sugary_beverages'] = ! empty($patientPppData->answers_for_eval['candy_sugary_beverages'])
            ? $patientPppData->answers_for_eval['candy_sugary_beverages']
            : 'N/A';
        if ($nutritionData['candy_sugary_beverages'] !== '0') {
            $candySugaryBeverages = $this->getTaskRecommendations($title, $index);
        }

        return $candySugaryBeverages;
    }

    public function evaluateAnswer11($patientPppData, $title)
    {
        $index                         = 0;
        $currentSmoker       = [];
        $smokingData['current_smoker'] = ! empty($patientPppData->answers_for_eval['current_smoker'])
            ? $patientPppData->answers_for_eval['current_smoker']
            : 'N/A';

        $smokingData['already_quit_smoking'] = ! empty($patientPppData->answers_for_eval['already_quit_smoking'])
            ? $patientPppData->answers_for_eval['already_quit_smoking']
            : 'N/A';

        if ($smokingData['current_smoker'] === 'Yes' && $smokingData['already_quit_smoking'] !== 'I already quit') {
            $currentSmoker = $this->getTaskRecommendations($title, $index);
        }

        return $currentSmoker;
    }

    public function evaluateAnswer2_4_11($patientPppData, $title)
    {
        $index                         = 1;
        $currentSmokerAge       = [];
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
        $formerSmoker       = [];
        $smokingData['current_smoker'] = ! empty($patientPppData->answers_for_eval['current_smoker'])
            ? $patientPppData->answers_for_eval['current_smoker']
            : 'N/A';

        $smokingData['already_quit_smoking'] = ! empty($patientPppData->answers_for_eval['already_quit_smoking'])
            ? $patientPppData->answers_for_eval['already_quit_smoking']
            : 'N/A';

        if ($smokingData['current_smoker'] === 'Yes' && $smokingData['already_quit_smoking'] === 'I already quit') {
            $formerSmoker = $this->getTaskRecommendations($title, $index);
        }

        return $formerSmoker;
    }


}