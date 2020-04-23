<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ListenToAwvChannel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const AWV_REPORT_CREATED          = 'awv-patient-report-created';
    const ENROLLMENT_SURVEY_COMPLETED = 'enrollable-survey-completed';
    private $channel;
    private $data;

    /**
     * ListenToAwvChannel constructor.
     * @param $data
     * @param $channel
     */
    public function __construct($data, $channel)
    {
        $this->data    = $data;
        $this->channel = $channel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ('enrollable-survey-completed' === $this->channel) {
            AwvPatientReportNotify::dispatch($this->data);
        }

        if ('enrollable-survey-completed' === $this->channel) {
            EnrollableSurveyCompleted::dispatch($this->data);
        }
    }
}
