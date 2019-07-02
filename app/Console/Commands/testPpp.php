<?php

namespace App\Console\Commands;

use App\Services\GeneratePersonalizedPreventionPlanService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class testPpp extends Command
{
    protected const TEST_USER_ID = 9784;

    /**
     * @var User
     */
    protected $patient;

    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @var GeneratePersonalizedPreventionPlanService
     */
    protected $service;

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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->date = Carbon::parse('2019-01-01');

        $this->patient = User
            ::with([
                'surveyInstances' => function ($instance) {
                    $instance->with(['survey', 'questions.type.questionTypeAnswers'])
                             ->forYear($this->date->year);
                },
                'answers'         => function ($answers) {
                    $answers->whereHas('surveyInstance', function ($instance) {
                        $instance->forYear($this->date->year);
                    });
                },
            ])
            ->findOrFail(self::TEST_USER_ID);

        $this->service = new GeneratePersonalizedPreventionPlanService($this->patient, $this->date);
    }
}
