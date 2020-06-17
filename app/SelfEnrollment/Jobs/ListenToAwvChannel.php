<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use App\Jobs\AwvNotifyBillingProviderOfCareDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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
        $this->data = $data;
        //remove prefix
        $this->channel = Str::replaceFirst(config('database.redis.options.prefix'), '', $channel);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (self::AWV_REPORT_CREATED === $this->channel) {
            AwvNotifyBillingProviderOfCareDocument::createFromAwvPatientReport($this->data)::dispatch();
        }

        if (self::ENROLLMENT_SURVEY_COMPLETED === $this->channel) {
            EnrollableSurveyCompleted::dispatch($this->data);
        }
    }
}
