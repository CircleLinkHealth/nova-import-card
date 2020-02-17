<?php

namespace App\Listeners;

use App\DirectMailMessage;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\Services\PhiMail\Incoming\Handlers\Pdf;
use App\Services\PhiMail\Incoming\Handlers\XML;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        
        }
        
        if ($this->hasG0506Pdf($event->directMailMessage->id)) {
            //@constantinos
            
        }
    }
    
    private function shouldBail(string $sender)
    {
        return ! str_contains($sender, '@upg.ssdirect.aprima.com');
    }
    
    private function hasG0506Pdf(int $dmId) :bool
    {
        return $this->mediaExists(DirectMailMessage::class, $dmId, Pdf::mediaCollectionNameFactory($dmId));
    }
    
    private function hasG0506Ccda(int $ccdaId)
    {
        return $this->mediaExists(Ccda::class, $ccdaId, XML::mediaCollectionNameFactory());
    }
    
    private function mediaExists(string $modelType, int $modelId, string $collectionName)
    {
        return Media::where('model_type', $modelType)->where('model_id', $modelId)->where('custom_properties->is_ccda', 'true')->where('custom_properties->is_upg0506', 'true')->exists();
    }
}
