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

class AwvPatientReportNotify implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $keysShouldExist = [
        'patient_id',
        'report_media_id',
    ];

    protected $patientReportData;

    /**
     * Create a new job instance.
     *
     * @param mixed $patientReportdata
     */
    public function __construct($patientReportdata)
    {
        $this->patientReportData = (array) json_decode($patientReportdata);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if ( ! is_array($this->patientReportData) || empty($this->patientReportData)) {
            \Log::error('Invalid patient report data received from AWV');

            return;
        }

        if ( ! array_keys_exist($this->keysShouldExist, $this->patientReportData)) {
            \Log::error('There are keys missing from patient report data received from AWV.');

            return;
        }

        $patient = User::ofType('participant')
            ->with('primaryPractice.settings')
            ->findOrFail($this->patientReportData['patient_id']);

        $media = Media::where('collection_name', 'patient-care-documents')
            ->where('model_id', $patient->id)
            ->whereIn('model_type', [\App\User::class, 'CircleLinkHealth\Customer\Entities\User'])
            ->find($this->patientReportData['report_media_id']);

        if ( ! $media) {
            \Log::error("Media with id: {$this->patientReportData['report_media_id']} not found for patient with id: {$patient->id}");

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
            AwvPatientReportNotify::class,
            'patient_id:'.$this->patientReportData['patient_id'],
            'report_media_id:'.$this->patientReportData['report_media_id'],
        ];
    }
}
