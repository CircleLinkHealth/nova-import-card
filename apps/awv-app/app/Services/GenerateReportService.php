<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Answer;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Illuminate\Support\Collection;

class GenerateReportService
{
    /**
     * @var Collection
     */
    protected $hraAnswers;

    /**
     * @var SurveyInstance
     */
    protected $hraInstance;

    /**
     * @var Collection
     */
    protected $hraQuestions;
    /**
     * @var \App\User
     */
    protected $patient;

    /**
     * @var Collection
     */
    protected $vitalsAnswers;

    /**
     * @var SurveyInstance
     */
    protected $vitalsInstance;

    /**
     * @var Collection
     */
    protected $vitalsQuestions;

    /**
     * GenerateReportService constructor.
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;

        $this->hraInstance    = $this->patient->surveyInstances->where('survey.name', Survey::HRA)->first();
        $this->vitalsInstance = $this->patient->surveyInstances->where('survey.name', Survey::VITALS)->first();

        $this->hraQuestions    = $this->hraInstance->questions;
        $this->vitalsQuestions = $this->vitalsInstance->questions;

        $this->hraAnswers    = $patient->answers->where('survey_instance_id', $this->hraInstance->id);
        $this->vitalsAnswers = $patient->answers->where('survey_instance_id', $this->vitalsInstance->id);
    }

    protected function answerForHraQuestionWithIdentifier(string $identifier, $default = [])
    {
        $question = $this->hraQuestions->where('identifier', $identifier)->first();

        $answer = $this->hraAnswers->where('question_id', $question->id)->first();

        return $this->sanitizedValue($answer, $default);
    }

    protected function answerForVitalsQuestionWithIdentifier(string $identifier, $subOrder = null)
    {
        $question = $this->vitalsQuestions->where('identifier', $identifier)->first();

        $answer = $this->vitalsAnswers->where('question_id', $question->id)->first();

        return $this->sanitizedValue($answer);
    }

    /**
     * @param Answer $answer
     *
     * @param array $default
     *
     * @return array|mixed
     */
    protected function sanitizedValue(Answer $answer = null, $default = [])
    {
        if ( ! $answer) {
            return $default;
        }

        $value = array_key_exists('value', $answer->value)
            ? $answer->value['value']
            : $answer->value;

        //sometimes we have arrays of 1 element which has [name=>null]
        if (is_array($value)) {
            return ProviderReportService::getArrayValue($value);
        }

        return $value;
    }
}
