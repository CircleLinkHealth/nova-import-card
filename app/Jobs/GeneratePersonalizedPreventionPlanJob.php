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

class GeneratePersonalizedPreventionPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var number
     */
    protected $patientId;

    /**
     * @var Carbon
     */
    protected $date;

    /**
     * Create a new job instance.
     *
     * @param $patientId
     * @param $date
     */
    public function __construct($patientId, $date)
    {
        $this->patientId = $patientId;
        $this->date      = Carbon::parse($date);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $patient = User
            ::with([
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
            ->findOrFail($this->patientId);

        $service = new GeneratePersonalizedPreventionPlanService($patient, $this->date);
        $service->generateData($patient);
    }
}
