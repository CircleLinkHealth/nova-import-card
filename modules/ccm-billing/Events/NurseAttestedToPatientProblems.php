<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Events;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\ProvidesAttestationData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NurseAttestedToPatientProblems implements ProvidesAttestationData
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected ?int $addendumId;

    protected array $attestedProblemIds;

    protected int $attestorId;

    protected ?int $callId;

    /**
     * Create a new event instance.
     */
    public function __construct(array $attestedProblemIds, int $attestorId, int $callId = null, int $addendumId = null)
    {
        $this->attestedProblemIds = $attestedProblemIds;
        $this->attestorId         = $attestorId;
        $this->callId             = $callId;
        $this->addendumId         = $addendumId;
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

    public function getAddendumId(): ?int
    {
        return $this->addendumId;
    }

    public function getAttestorId(): ?int
    {
        return $this->attestorId;
    }

    public function getCallId(): ?int
    {
        return $this->callId;
    }

    public function getChargeableMonth(): ?Carbon
    {
        return null;
    }

    //todo: deprecate
    public function getPmsId(): ?int
    {
        return null;
    }

    public function getProblemIds(): array
    {
        return $this->attestedProblemIds;
    }
}
