<?php

namespace App\Services;


use App\Survey;
use App\TaskRecommendations;
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


    protected $hraAnswers;
    protected $vitalsAnswers;


    public function __construct(User $patient)
    {
        $this->patient        = $patient;
        $this->hraInstance    = $patient->surveyInstances->where('survey.name', Survey::HRA)->first();
        $this->vitalsInstance = $patient->surveyInstances->where('survey.name', Survey::VITALS)->first();
        $this->hraAnswers     = $patient->answers->where('survey_instance_id', $this->hraInstance->id);
        $this->vitalsAnswers  = $patient->answers->where('survey_instance_id', $this->vitalsInstance->id);
        //@todo::remove this when done dev
        $this->generateData($patient);
    }

    public function generateData($patient)
    {
        $birthDate = new Carbon('2019-01-01');

        //get hra data
        $hraAnswers = $this->hraAnswers;

        //get all recommendations
        $data                = [];
        $taskRecommendations = TaskRecommendations::all();
        $recommendations     = $taskRecommendations->map(function ($recommendation) {
            return [
                'id'              => $recommendation['id'],
                'title'           => $recommendation['title'],
                'rec_task_titles' => $recommendation['rec_task_titles'],
                'data'            => $recommendation['data'],
                'codes'           => $recommendation['codes'],
            ];
        });

        foreach ($recommendations as $recommendation) {
            foreach ($recommendation['data'] as $rec => $conditions) {
                return $conditions['trigger_conditions'];
            }
        }
        //for each recommendation


        //go through each expression and build query using hra values

        //use hra values to evaluate expressions

        $patientPppData = $this->patient
            ->personalizedPreventionPlan()
            ->create([
                'user_id'          => $patient->id,
                'display_name'     => $patient->display_name,
                'birth_date'       => /*$patient->patientInfo->birth_date*/
                    $birthDate,
                'address'          => $patient->address,
                'city'             => $patient->city,
                'state'            => $patient->state,
                'billing_provider' => /*$patient->billingProvider->member_user_id*/
                    'Kirkillis',
                'hra'              => $this->hraAnswers,
                'vitals'           => $this->vitalsAnswers,
                /* 'recommendations'  => [],*/
            ]);

        return $patientPppData;
    }
}


