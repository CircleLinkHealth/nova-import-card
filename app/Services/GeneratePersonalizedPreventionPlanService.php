<?php

namespace App\Services;


use App\Survey;
use Carbon\Carbon;

class GeneratePersonalizedPreventionPlanService
{
    protected $patient;
    protected $hraInstance;
    protected $vitalsInstance;


    public function __construct($patient)
    {
        $this->patient = $patient;

        $this->hraInstance    = $patient->surveyInstances->where('survey.name', Survey::HRA)->first();
        $this->vitalsInstance = $patient->surveyInstances->where('survey.name', Survey::VITALS)->first();

        $this->hraAnswers    = $patient->answers->where('survey_instance_id', $this->hraInstance->id);
        $this->vitalsAnswers = $patient->answers->where('survey_instance_id', $this->vitalsInstance->id);
        $this->generateData($patient);
    }

    public function generateData($patient)
    {

        $birthDate = new Carbon('2019-01-01');

        $patientPppData = $this->patient
            ->personalizedPreventionPlan()
            ->create([
                'user_id'          => $patient->id,
                'display_name'     => $patient->display_name,
                'birth_date'       => $birthDate/*$patient->patientInfo->birth_date*/,
                'address'          => $patient->address,
                'city'             => $patient->city,
                'state'            => $patient->state,
                'billing_provider' => 'Mr. BEan'/*$patient->billingProvider->member_user_id*/,
                'hra_values'       => $this->hraAnswers,
                'vitals_values'    => $this->vitalsAnswers,
            ]);

        return $patientPppData;
    }
}


