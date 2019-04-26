<?php

namespace App\Jobs;

use App\Services\GeneratePersonalizedPreventionPlanService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PersonalizedPreventionPlan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $patient;
    protected $date;
    protected $service;

    /**
     * Create a new job instance.
     *
     * @param $patientId
     * @param $date
     */
    public function __construct($patientId, $date)
    {
        $this->date = Carbon::parse($date);

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

        ])
                             ->findOrFail($patientId);

        $this->service = new GeneratePersonalizedPreventionPlanService($this->patient, $this->date);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->service->generateData($this->patient);
    }
}
