<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ListenToAwvChannel implements ShouldQueue
{
    const AWV_REPORT_CREATED = 'awv-patient-report-created';
    const ENROLLMENT_SURVEY_COMPLETED = 'enrollable-survey-completed';

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $data;
    private $channel;

    /**
     * ListenToAwvChannel constructor.
     * @param $data
     * @param $channel
     */
    public function __construct($data, $channel)
    {
        $this->data = $data;
        $this->channel = $channel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->channel === self::AWV_REPORT_CREATED) {
            AwvPatientReportNotify::dispatch($this->data);
        }

        if ($this->channel === self::ENROLLMENT_SURVEY_COMPLETED) {
            EnrollableSurveyCompleted::dispatch($this->data);
        }
    }
}
