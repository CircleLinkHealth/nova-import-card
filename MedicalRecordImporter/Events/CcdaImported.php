<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/17/20
 * Time: 1:21 AM
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Events;

use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CcdaImported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var int
     */
    public $ccdaId;
    
    /**
     * CcdaImported constructor.
     *
     * @param int $ccdaId
     */
    public function __construct(int $ccdaId)
    {
        $this->ccdaId = $ccdaId;
    }
    
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}