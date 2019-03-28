<?php

namespace App\Jobs;

use App\ProviderReport;
use App\Services\ProviderReportService;

use Carbon\Carbon;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateProviderReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Patient to attempt to create provider report for.
     *
     * @var array
     */
    protected $patientId;

    /**
     * Date to specify for which survey instances to generate Provider Report for.
     *
     * @var Carbon
     */
    protected $date;


    protected $service;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($patientId, $date)
    {
        $this->patientId = $patientId;

        $this->date = Carbon::parse($date);

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //check if it has a report already with those instances
        $existingReport = ProviderReport::whereHas('hraSurveyInstance', function ($hra) {
            $hra->forDate($this->date);
        })
                                        ->whereHas('vitalsSurveyInstance', function ($hra) {
                                            $hra->forDate($this->date);
                                        })
                                        ->where('patient_id', $this->patientId)
                                        ->first();

        $patient = User::with([
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


        $service = new ProviderReportService($patient, $this->date, $existingReport);

        $report = $service->generateData();

        //slack/notify something/someone
    }
}
