<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2019-04-21
 * Time: 01:44
 */

namespace App\Services;


class GetSurveyAnswersForEvaluation
{
    public function getAnswersForEvaluation($patientPppData)
    {
        $nutritionData['fruit_veggies'] = ! empty($patientPppData->answers_for_eval['fruit_veggies'])
            ? $patientPppData->answers_for_eval['fruit_veggies']
            : 'N/A';

        return $nutritionData['fruit_veggies'];
    }
}