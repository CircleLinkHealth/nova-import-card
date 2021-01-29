<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateAwvSurveyInstances extends Command
{
    const ENROLLEE_SURVEY = 'Enrollees';
    const HRA = 'HRA';
    const VITALS = 'Vitals';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:survey-current-instance {surveyName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the Awv Survey Instances and Survey Questions instances for Current Year.';
    /**
     * @var array|string|null
     */
    private $surveyName;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->surveyName = $this->argument('surveyName');

        if (!$this->surveyName){
            $this->error("Survey name is required.");
            return;
        }

        $survey = \DB::table('surveys')->where('name', $this->surveyName)->select('id')->first();

        if (!$survey){
            $this->error("Survey $this->surveyName not found in surveys table.");
            return;
        }

        $currentSurveyQuestionsInstance =  $this->updateSurveyInstance($survey->id);

        if (!$currentSurveyQuestionsInstance){
            $this->error("Current Survey Questions Instance not found");
            return;
        }

        $this->copySurveyQuestionsForCurrentInstance($survey->id, $currentSurveyQuestionsInstance->id);
    }

    /**
     * @param int $surveyId
     */
    private function updateSurveyInstance(int $surveyId)
    {
        $currentYear = Carbon::now()->year;
        $created =  DB::table('survey_instances')->updateOrInsert([
            'survey_id' => $surveyId,
            'year'=> $currentYear
        ]);

        if (! $created){
            $this->error("Something went wrong. Nothing was created!");
            return;
        }

        $this->info("$this->surveyName instance was created for $currentYear");
        return DB::table('survey_instances')->where('year', $currentYear)->first();
    }

    private function copySurveyQuestionsForCurrentInstance(int $surveyId, int $currentSurveyQuestionsInstanceId)
    {
        $previousYear = Carbon::now()->subYear()->year;
        $surveyInstance = DB::table('survey_instances');
        $previousYearInstance = $surveyInstance
            ->where('survey_id', $surveyId)
            ->where('year', $previousYear)
            ->select('id')
            ->first();

        if (!$previousYearInstance){
            $this->error("Previous year's instance is missing. Cannot create Survey Questions for current instance.");
            return;
        }

        $previousSurveyQuestionsInstance = DB::table('survey_questions')
            ->where('survey_instance_id', $previousYearInstance->id)
            ->get();

        if (!$previousSurveyQuestionsInstance){
         $this->error("Previous Survey Questions instance missing. Cannot create Survey Questions for current instance.");
         return;
        }

        foreach ($previousSurveyQuestionsInstance as $question){
            DB::table('survey_questions')
                ->updateOrInsert([
                    'survey_instance_id'=> $currentSurveyQuestionsInstanceId,
                    'question_id'=>$question->id,
                ],
                    [

                        'order'=>$question->order,
                        'sub_order'=>$question->sub_order,
                    ]
                );
        }
    }
}
