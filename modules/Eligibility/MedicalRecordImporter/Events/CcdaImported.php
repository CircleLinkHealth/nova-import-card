<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CcdaImported
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    /**
     * @var int
     */
    public $ccdaId;

    /**
     * CcdaImported constructor.
     */
    public function __construct(int $ccdaId)
    {
        $this->ccdaId = $ccdaId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
