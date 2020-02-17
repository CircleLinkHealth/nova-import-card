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

class UPG0506WorkflowListener implements ShouldQueue
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
        
        //@constantinos
        if ($this->hasPdfAttachment($event->directMailMessage->id)) {
        
        }
    
        if ($this->hasCcdaAttachment($event->directMailMessage->id)) {
        
        }
    }
    
    private function shouldBail(string $sender)
    {
        return ! str_contains($sender, '@upg.ssdirect.aprima.com');
    }
    
    private function hasPdfAttachment(int $dmId) :bool
    {
        return $this->mediaExists(DirectMailMessage::class, $dmId, Pdf::mediaCollectionNameFactory($dmId));
    }
    
    private function hasCcdaAttachment(int $ccdaId)
    {
        return $this->mediaExists(Ccda::class, $ccdaId, XML::mediaCollectionNameFactory());
    }
    
    private function mediaExists(string $modelType, int $modelId, string $collectionName)
    {
        Media::whereModelType($modelType)->whereModelId($modelId)->whereCollectionName(
            $collectionName
        )->exists();
    }
}
