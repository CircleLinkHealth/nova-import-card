<?php

namespace App\Console\Commands;

use App\Services\GeneratePersonalizedPreventionPlanService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class testPpp extends Command
{
    protected $patient;
    protected $date;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testPpp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->date = Carbon::parse('2019-01-01');

        $this->patient = User::with([
            'surveyInstances' => function ($instance) {
                $instance->with(['survey', 'questions.type.questionTypeAnswers'])
                         ->forDate($this->date);
            },
            'answers'         => function ($answers) {
                $answers->whereHas('surveyInstance', function ($instance) {
                    $instance->forDate($this->date);
                });
            },
            'providerReports' => function ($report) {
                $report->whereHas('hraSurveyInstance', function ($instance) {
                    $instance->forDate($this->date);
                })
                       ->whereHas('vitalsSurveyInstance', function ($instance) {
                           $instance->forDate($this->date);
                       });
            },
        ])
                             ->findOrFail(9784);

        $this->service = new GeneratePersonalizedPreventionPlanService($this->patient, $this->date);

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

    }
}
