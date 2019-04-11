<?php

namespace App\Services;


use App\Survey;
use Carbon\Carbon;

class GeneratePersonalizedPreventionPlanService
{
    protected $patient;
    protected $patientName;
    protected $userId;
    protected $birthDate;
    protected $address;
    protected $billingProvider;

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
        $date    = Carbon::parse('2019-01-01')->toDateString();
        $pppData = $this->patient
            ->personalizedPreventionPlan()
            ->create([
                'user_id'          => $this->userId,
                'display_name'     => $patient->display_name,
                'birth_date'       => $date/*$patient->patientInfo->birth_date*/
                ,
                'address'          => $patient->address,
                'billing_provider' => '322'/*$patient->billingProvider->member_user_id*/,
                'hra_values'       => $this->hraAnswers,
                'vitals_values'    => $this->vitalsAnswers,
            ]);

        return $pppData;
    }
}


