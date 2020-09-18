<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\SharedModels\Entities\DirectMailMessage;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class UPG0506CcdaImporterListener implements ShouldQueue
{
    use InteractsWithQueue;

    const UPG_NAME = 'UPG';

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
    public function handle(CcdaImported $event)
    {
        $ccda = Ccda::find($event->ccdaId);
        if ( ! $ccda || $this->shouldBail($ccda)) {
            return;
        }

        Media::where('model_type', Ccda::class)->where('model_id', $ccda->id)->chunkById(
            10,
            function ($medias) {
                $medias->each(
                    function ($media) {
                        $data = $media->custom_properties;
                        $data['is_ccda'] = 'true';
                        $data['is_upg0506'] = 'true';
                        $data['is_upg0506_complete'] = 'false';
                        $media->custom_properties = $data;
                        $media->save();
                    }
                );
            }
        );
    }

    private function shouldBail(Ccda $ccda): bool
    {
        return ! (Str::contains(
            optional(DirectMailMessage::find($ccda->direct_mail_message_id))->from,
            '@upg.ssdirect.aprima.com'
        ) && $ccda->hasProcedureCode('G0506'));
    }
}
