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


        return true;
    }
}