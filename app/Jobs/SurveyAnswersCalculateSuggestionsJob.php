<?php

namespace App\Jobs;

use App\Services\SurveyAnswerSuggestionsCalculator;
use App\Services\SurveyService;
use App\Survey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SurveyAnswersCalculateSuggestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $patientId;

    /**
     * Create a new job instance.
     *
     * @param $patientId
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
    public function handle()
    {
        $surveys = Survey::where('name', '=', Survey::HRA)
                         ->orWhere('name', '=', Survey::VITALS)
                         ->get()
                         ->mapWithKeys(function ($survey) {
                             return [$survey->name => $survey->id];
                         });

        //could optimise these, but I tried to re-use existing code
        $userWithHraSurvey    = SurveyService::getSurveyData($this->patientId, $surveys[Survey::HRA]);
        $userWithVitalsSurvey = SurveyService::getSurveyData($this->patientId, $surveys[Survey::VITALS]);
        $calculator           = new SurveyAnswerSuggestionsCalculator($userWithHraSurvey,
            $userWithHraSurvey->surveyInstances->first(),
            $userWithVitalsSurvey->surveyInstances->first());
        $calculator->calculate();
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['Survey Answers Calculate Suggestions', $this->patientId];
    }
}
