<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

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
    protected $signature = 'create:awv-survey-instances {surveyName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the Awv Survey Instances for Current Year.';

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
        $surveyName = $this->argument('surveyName');

        if (!$surveyName){
            $this->error("Survey name is required.");
            return;
        }

        $survey = \DB::table('surveys')->where('name', $surveyName)->select('id')->first();

        if (!$survey){
            $this->error("Survey $surveyName not found in surveys table.");
            return;
        }

        $year = Carbon::now()->year;

        $created =  \DB::table('survey_instances')->updateOrInsert([
           'survey_id' => $survey->id,
           'year'=> $year
        ]);

        if (! $created){
            $this->error("Something went wrong. Nothing was created!");
            return;
        }

        $this->info("$surveyName instance was created for $year");

    }
}
