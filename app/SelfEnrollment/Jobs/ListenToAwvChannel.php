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
     * @throws \Exception
     * @return void
     */
    public function handle()
    {
        if (self::AWV_REPORT_CREATED === $this->channel) {
            $data = $this->decodeCareDocumentJsonData($this->data);
            AwvNotifyBillingProviderOfCareDocument::dispatch($data['patient_id'], $data['report_media_id']);
        }

        if (self::ENROLLMENT_SURVEY_COMPLETED === $this->channel) {
            //todo: better decode data here, so the EnrollableSurveyCompleted can be tested without worrying about corrupted data
            EnrollableSurveyCompleted::dispatch($this->data);
        }
    }

    private function decodeCareDocumentJsonData(?string $patientReportdata)
    {
        $decoded = json_decode($patientReportdata, true);

        if ( ! is_array($decoded) || empty($decoded)) {
            throw new \Exception('Invalid patient report data received from AWV');
        }

        if ( ! array_keys_exist([
            'patient_id',
            'report_media_id',
        ], $decoded)) {
            throw new \Exception('There are keys missing from patient report data received from AWV.');
        }

        return $decoded;
    }
}
