<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\DirectMailMessage;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\Services\PhiMail\Incoming\Handlers\Pdf;
use App\UPG\UPGPdfCarePlan;
use CircleLinkHealth\Customer\Entities\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class UPG0506DirectMailListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(DirectMailMessageReceived $event)
    {
        if ($this->shouldBail($event->directMailMessage->from)) {
            return;
        }

        if ($this->hasG0506Pdf($event->directMailMessage->id)) {
            $this->parseAndUpdatePdfMedia($event->directMailMessage->id);
        }
    }

    private function getG0506Pdf(int $dmId): ?Media
    {
        return $this->mediaAttachmentCollectionQuery($dmId)->first();
    }

    private function hasG0506Pdf(int $dmId): bool
    {
        return $this->mediaAttachmentCollectionQuery($dmId)->exists();
    }

    private function mediaAttachmentCollectionQuery(int $dmId)
    {
        return $this->mediaQuery(DirectMailMessage::class, $dmId)->where(
            'collection_name',
            Pdf::mediaCollectionNameFactory($dmId)
        );
    }

    private function mediaQuery(string $modelType, int $modelId)
    {
        return Media::where('model_type', $modelType)->where('model_id', $modelId);
    }

    private function parseAndUpdatePdfMedia(int $dmId)
    {
        $pdf = $this->getG0506Pdf($dmId);

        $filePath = storage_path($pdf->file_name);
        file_put_contents($filePath, $pdf->getFile());

        $carePlan = (new UPGPdfCarePlan($pdf->file_name))->read()->toArray();

        if ( ! empty($carePlan)) {
            //create constants for these keys?
            $data                        = $pdf->custom_properties;
            $data['is_pdf']              = 'true';
            $data['is_upg0506']          = $carePlan['is_g0506'];
            $data['is_upg0506_complete'] = 'false';
            $data['mrn']                 = $carePlan['demographics']['mrn_number'];
            $data['care_plan']           = $carePlan;
            $pdf->custom_properties      = $data;
            $pdf->save();
        }

        unlink($filePath);
    }

    private function shouldBail(string $sender)
    {
        return ! Str::contains($sender, '@upg.ssdirect.aprima.com') || ! upg0506IsEnabled();
    }
}
