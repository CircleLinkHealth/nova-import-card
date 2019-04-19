<?php

namespace App\Services;


use App\Survey;
use App\User;
use Carbon\Carbon;

class GeneratePersonalizedPreventionPlanService
{
    /**
     * @var \App\User
     */
    protected $patient;
    protected $hraInstance;
    protected $vitalsInstance;
    protected $hraQuestions;
    protected $vitalsQuestions;
    protected $hraAnswers;
    protected $vitalsAnswers;


    public function __construct(User $patient)
    {
        $this->patient         = $patient;
        $this->hraInstance     = $patient->surveyInstances->where('survey.name', Survey::HRA)->first();
        $this->vitalsInstance  = $patient->surveyInstances->where('survey.name', Survey::VITALS)->first();
        $this->hraAnswers      = $patient->answers->where('survey_instance_id', $this->hraInstance->id);
        $this->vitalsAnswers   = $patient->answers->where('survey_instance_id', $this->vitalsInstance->id);
        $this->hraQuestions    = $this->hraInstance->questions;
        $this->vitalsQuestions = $this->vitalsInstance->questions;

        //@todo::remove this when done dev
        $this->generateData($patient);
    }

    public function generateData($patient)
    {
        $birthDate = new Carbon('2019-01-01');

        $patientPppData = $this->patient
            ->personalizedPreventionPlan()
            ->create([
                'user_id'              => $patient->id,
                'display_name'         => $patient->display_name,
                'birth_date'           => /*$patient->patientInfo->birth_date*/
                    $birthDate,
                'address'              => $patient->address,
                'city'                 => $patient->city,
                'state'                => $patient->state,
                'hra_answers'          => $this->hraAnswers,
                'vitals_answers'       => $this->vitalsAnswers,
                'billing_provider'     => /*$patient->billingProvider->member_user_id*/
                    'Kirkillis',
                'answers_for_eval' => $this->getAnswersToEvaluate(),

            ]);

        return $patientPppData;
    }

    private function getAnswersToEvaluate()
    {
        $data                             = [];
        $data['age']                      = $this->answerForHraQuestionWithBody('What is your age?');
        $data['sex']                      = $this->answerForHraQuestionWithBody('What is your sex?');
        $data['fruit_veggies']            = $this->answerForHraQuestionWithBody('In the past 7 days, how many servings of fruits and vegetables did you typically eat each day? (1 serving = 1 cup of fresh vegetables, 1/2 cup of cooked vegetables, or 1 medium piece of fruit. 1 cup = size of a baseball).');
        $data['whole_grain']              = $this->answerForHraQuestionWithBody('In the past 7 days, how many servings of high fiber or whole grain foods did you typically eat each day? (1 serving = 1 slice of 100% of whole wheat bread, 1 cup of whole-grain or high fiber ready to eat cereal, 1/2 cup of cooked cereal such as oatmeal, or 1/2 cup of cooked brown rice or whole wheat pasta).');
        $data['fatty_fried_foods']        = $this->answerForHraQuestionWithBody('In the past 7 days, how many servings of fried or high-fat foods did you typically eat each day? (Examples include fried chicken, fried fish, bacon, French fries, potato chips, corn chips, doughnuts, creamy salad dressings, and foods made with whole milk, cream, cheese, or mayonnaise).');
        $data['candy_sugary_beverages']   = $this->answerForHraQuestionWithBody('In the past 7 days, how many sugar-sweetened (not diet) beverages and candy servings did you typically consume each day?');
        $data['diabetes']                 = $this->answerForHraQuestionWithBody('Please check/uncheck if you have ever had the following conditions:');
        $data['current_smoker']           = $this->answerForHraQuestionWithBody('Do or did you ever smoke or use any tobacco products (cigarettes, chew, snuff, pipes, cigars, vapor/e-cigarettes)?');
        $data['already_quit_smoking']     = $this->answerForHraQuestionWithBody('Are you interested in quitting?');
        $data['alcohol_drinks']           = $this->answerForHraQuestionWithBody('On average, how many alcoholic beverages do you consume per week? (One standard drink is defined as 12.0 oz of beer, 5.0 oz of wine, or 1.5 oz of liquor)');
        $data['recreational_drugs']       = $this->answerForHraQuestionWithBody('Have you used recreational drugs in the past year?');
        $data['physical_activity']        = $this->answerForHraQuestionWithBody('How often do you exerise?');
        $data['sexually_active']          = $this->answerForHraQuestionWithBody('Are you sexually active?');
        $data['multiple_partners']        = $this->answerForHraQuestionWithBody('Do you have multiple sexual partners');
        $data['safe_sex']                 = $this->answerForHraQuestionWithBody('Are you practicing safe sex?');
        $data['domestic_violence_screen'] = $this->answerForHraQuestionWithBody('When was the last time you had an Intimate Partner Violence/Domestic Violence Screening?');
        $data['difficulty_hearing']       = $this->answerForHraQuestionWithBody('Do you have difficulty with your hearing?');
        $data['fallen']                   = $this->answerForHraQuestionWithBody('Have you fallen in the past 6 months? (a fall is when the body goes to the ground without being pushed)');
        $data['bmi']                      = $this->answerForVitalsQuestionWithBody('What is the patient\'s body mass index (BMI)?');

        return $data;
    }

    private function answerForHraQuestionWithBody($body)
    {
        $question = $this->hraQuestions->where('body', $body)->first();

        $answer = $this->hraAnswers->where('question_id', $question->id)->first();

        if ( ! $answer) {
            return [];
        }

        return array_key_exists('value', $answer->value)
            ? $answer->value['value']
            : $answer->value;
    }

    private function answerForVitalsQuestionWithBody($body)
    {
        $question = $this->vitalsQuestions->where('body', $body)->first();
        $answer   = $this->vitalsAnswers->where('question_id', $question->id)->first();

        if ( ! $answer) {
            return [];
        }

        return array_key_exists('value', $answer->value)
            ? $answer->value['value']
            : $answer->value;

    }
}


