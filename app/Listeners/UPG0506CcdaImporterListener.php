<?php

namespace App\Listeners;

use App\DirectMailMessage;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UPG0506CcdaImporterListener
{
    const UPG_NAME = 'UPG';
    
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    /**
     * Handle the event.
     *
     * @param  object $event
     *
     * @return void
     */
    public function handle(CcdaImported $event)
    {
        if ($this->shouldBail($event->ccda)) {
            return;
        }
        
        Media::where('model_type', Ccda::class)->where('model_id', $event->ccda->id)->chunkById(
            10,
            function ($medias) {
                $medias->each(
                    function ($media) {
                        $data                     = $media->custom_properties;
                        $data['is_ccda']          = 'true';
                        $data['is_upg0506']       = 'true';
                        $media->custom_properties = $data;
                        $media->save();
                    }
                );
            }
        );
    }
    
    private function shouldBail(Ccda $ccda)
    {
        return ! (str_contains(optional(DirectMailMessage::find($ccda->direct_mail_message_id))->from, '@upg.ssdirect.aprima.com') && $ccda->hasProcedureCode('G0506'));
    }
}
