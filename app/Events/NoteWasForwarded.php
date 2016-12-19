<?php

namespace App\Events;

use App\Note;
use App\User;
use Illuminate\Queue\SerializesModels;

class NoteWasForwarded extends Event
{
    use SerializesModels;
    public $patient;
    public $sender;
    public $note;
    public $careteam;

    /**
     * Create a new event instance.
     *
     * @param User $patient
     * @param User $sender
     * @param Note $note
     */
    public function __construct(
        Note $note
    ) {
        $this->note = $note;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
