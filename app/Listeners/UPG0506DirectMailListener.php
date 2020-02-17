<?php

namespace App\Listeners;

use App\DirectMailMessage;
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
    
        if ($this->hasG0506Ccda($event->directMailMessage->id)) {
            // @constantinos
            //
            // If we got here it means the CCD has been imported, and has
            // custom_properties->is_upg0506 = 'true'
            // custom_properties->is_ccda = 'true'
            
            // Let's use the following status
            // custom_properties->is_upg0506_complete = false
            // custom_properties->is_upg0506_complete = true
        }
        
        if ($this->hasG0506Pdf($event->directMailMessage->id)) {
            // @constantinos
            //
            // 1. Parse PDF
            // 2. Store media
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
    
    private function hasG0506Ccda(int $dmId)
    {
        return Ccda::where('direct_mail_message_id', $dmId)->hasUPG0506Media()->exists();
    }
    
    private function mediaQuery(string $modelType, int $modelId)
    {
        return Media::where('model_type', $modelType)->where('model_id', $modelId);
    }
}
