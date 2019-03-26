<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 22/03/2019
 * Time: 3:15 PM
 */

namespace App\Services;


use App\Survey;
use Carbon\Carbon;

class ProviderReportService
{
    protected $patient;

    protected $date;

    protected $hraAnswers;

    protected $vitalsAnswers;



    public function __construct($patient, $date)
    {
        //patient contains survey data (answers with related questions,types,type_answers)
        $this->patient = $patient;
        $this->date    = Carbon::parse($date);

        $hraInstance    = $this->patient->surveyInstances->where('survey', function ($s) {
            $s->where('name', Survey::HRA);
        });
        $vitalsInstance = $this->patient->surveyInstances->where('survey', function ($s) {
            $s->where('name', Survey::VITALS);
        });

        $this->hraAnswers    = $patient->answers->where('survey_instance_id', $hraInstance->id);
        $this->vitalsAnswers = $patient->answers->where('survey_instance_id', $vitalsInstance->id);

    }

    public function generateData(){





        //todo: fix
        $reasonForVisit = 'Inital';

        $demographicData = $this->createDemographicData();

        $this->patient->providerReports()->updateOrCreate(
            [
                'hra_instance_id',
                'vitals_instance_id',
                'reason_for_visit',
                'demographic_data',
                'allergy_history',
                'medical_history',
                'medication_history',
                'family_medical_history',
                'immunization_history',
                'screenings',
                'mental_state',
                'vitals',
                'diet',
                'social_factors',
                'sexual_activity',
                'exercise_activity_levels',
                'functional_capacity',
                'current_providers',
                'specific_patient_requests',
            ]
        );

        return true;
    }

    //or update?
    public function createDemographicData(){

    }
}