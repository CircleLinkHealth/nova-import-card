<?php

namespace App\Jobs;

use App\Services\SurveyAnswerSuggestionsCalculator;
use App\Services\SurveyService;
use App\Survey;
use App\SurveyInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SurveyAnswersCalculateSuggestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $patientId;

    /** @var SurveyService $service */
    protected $service;

    /**
     * Create a new job instance.
     *
     * @param $patientId
     * @param SurveyService $service
     */
    public function __construct($patientId)
    {
        $this->patientId = $patientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SurveyService $service)
    {
        $this->service = $service;
        $surveys       = Survey::where('name', '=', Survey::HRA)
                               ->orWhere('name', '=', Survey::VITALS)
                               ->get()
                               ->mapWithKeys(function ($survey) {
                                   return [$survey->name => $survey->id];
                               });

        $this->setSuggestionsForHRA($surveys[Survey::HRA]);
        $this->setSuggestionsForVitals($surveys[Survey::VITALS]);
    }

    private function setSuggestionsForHRA($surveyId)
    {
        $userSurvey = SurveyService::getSurveyData($this->patientId, $surveyId);

        /** @var SurveyInstance $surveyInstance */
        $surveyInstance = $userSurvey->surveyInstances->first();
        if ( ! $surveyInstance) {
            return;
        }

        $calculator = new SurveyAnswerSuggestionsCalculator($userSurvey, $surveyInstance);
        $calculator->calculate();
    }

    private function setSuggestionsForVitals($surveyId)
    {

    }
}
