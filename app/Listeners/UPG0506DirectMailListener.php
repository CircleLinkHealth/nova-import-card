<?php

namespace App\Listeners;

use App\DirectMailMessage;
use App\Jobs\DecorateUPG0506CcdaWithPdfData;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\Services\PhiMail\Incoming\Handlers\Pdf;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        //
    }
    
    /**
     * Handle the event.
     *
     * @param  object $event
     *
     * @return void
     */
    public function handle(DirectMailMessageReceived $event)
    {
        if ($this->shouldBail($event->directMailMessage->from)) {
            return;
        }
    
        if ($ccd = $this->getG0506Ccda($event->directMailMessage->id)) {
            // @constantinos
            //
            // If we got here it means the CCD has been imported, and has
            // custom_properties->is_upg0506 = 'true'
            // custom_properties->is_ccda = 'true'
            
            // Let's use the following status
            // custom_properties->is_upg0506_complete = false
            // custom_properties->is_upg0506_complete = true
            DecorateUPG0506CcdaWithPdfData::dispatch($ccd);
        }
        
        if ($this->hasG0506Pdf($event->directMailMessage->id)) {
            // @constantinos
            // @todo
            // 1. Parse PDF
            // 2. Store media and add the following varibles
            // custom_properties->is_upg0506 = 'true'
            // custom_properties->is_pdf = 'true'
            // custom_properties->is_upg0506_complete = false
            // custom_properties->is_upg0506_complete = true
        }
    }
    
    private function shouldBail(string $sender)
    {
        return ! str_contains($sender, '@upg.ssdirect.aprima.com');
    }
    
    private function hasG0506Pdf(int $dmId) :bool
    {
        return $this->mediaQuery(DirectMailMessage::class, $dmId)->where('collection_name', Pdf::mediaCollectionNameFactory($dmId))->exists();
    }
    
    private function getG0506Ccda(int $dmId)
    {
        return $this->ccdaQuery($dmId)->first();
    }
    
    private function mediaQuery(string $modelType, int $modelId)
    {
        return Media::where('model_type', $modelType)->where('model_id', $modelId);
    }
    
    private function ccdaQuery(int $dmId)
    {
        return Ccda::where('direct_mail_message_id', $dmId)->hasUPG0506Media();
    }
}
