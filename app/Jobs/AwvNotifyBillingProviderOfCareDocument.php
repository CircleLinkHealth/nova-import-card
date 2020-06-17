<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\Channels\FaxChannel;
use App\Notifications\SendCareDocument;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AwvNotifyBillingProviderOfCareDocument implements ShouldQueue
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
     *
     * @param mixed $patientReportdata
     */
    public function __construct(int $patientUserId, int $reportMediaId)
    {
        $this->patientUserId = $patientUserId;
        $this->reportMediaId = $reportMediaId;
    }

    public static function createFromAwvPatientReport(?string $patientReportdata)
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

        return new static($decoded['patient_id'], $decoded['report_media_id']);
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
            $channels[] = FaxChannel::class;
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
