<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Jobs;

use CircleLinkHealth\Core\Notifications\Channels\DirectMailChannel;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Notifications\SendCareDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AwvNotifyBillingProviderOfCareDocument implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $patientReportData;
    /**
     * @var int
     */
    private $patientUserId;
    /**
     * @var int
     */
    private $reportMediaId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $patientUserId, int $reportMediaId)
    {
        $this->patientUserId = $patientUserId;
        $this->reportMediaId = $reportMediaId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $patient = User::ofType('participant')
            ->with('primaryPractice.settings')
            ->findOrFail($this->patientUserId);

        $media = Media::where('collection_name', 'patient-care-documents')
            ->where('model_id', $patient->id)
            ->whereIn('model_type', [\App\User::class, 'CircleLinkHealth\Customer\Entities\User'])
            ->find($this->reportMediaId);

        if ( ! $media) {
            \Log::error("Media with id: {$this->reportMediaId} not found for patient with id: {$patient->id}");

            return;
        }

        $billingProvider = $patient->billingProviderUser();

        if ( ! $billingProvider) {
            \Log::error("No billing provider found for patient with id: {$patient->id}");

            return;
        }

        if ( ! $patient->primaryPractice) {
            \Log::error("No Primary Practice found for patient with id: {$patient->id}");

            return;
        }

        $settings = $patient->primaryPractice->settings->first();

        $channels = [];

        if ($settings->email_awv_reports) {
            $channels[] = MailChannel::class;
        }

        if ($settings->dm_awv_reports) {
            $channels[] = DirectMailChannel::class;
        }

        if ($settings->efax_awv_reports) {
            $channels[] = 'phaxio';
        }

        if (empty($channels)) {
            return;
        }

        $billingProvider->notify(new SendCareDocument($media, $patient, $channels));
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            AwvNotifyBillingProviderOfCareDocument::class,
            'patient_id:'.$this->patientReportData['patient_id'],
            'report_media_id:'.$this->patientReportData['report_media_id'],
        ];
    }
}