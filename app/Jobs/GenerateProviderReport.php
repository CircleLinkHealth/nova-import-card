<?php

namespace App\Jobs;

use App\Services\GenerateProviderReportService;
use App\User;
use Carbon\Carbon;
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
    protected $patient;

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
            'providerReports' => function ($report) {
                $report->whereHas('hraSurveyInstance', function ($instance) {
                    $instance->forDate($this->date);
                })
                       ->whereHas('vitalsSurveyInstance', function ($instance) {
                           $instance->forDate($this->date);
                       });
            },
        ])
                             ->findOrFail($patientId);

        $this->service = new GenerateProviderReportService($this->patient, $this->date);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $report = $this->service->generateData();

        //slack/notify something/someone
    }
}
