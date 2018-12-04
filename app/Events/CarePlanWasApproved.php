<?php

namespace App\Events;

use App\User;
use Illuminate\Queue\SerializesModels;

class CarePlanWasApproved extends Event
{
    use SerializesModels;

    public $patient;
    public $practiceSettings;

    /**
     * Create a new event instance.
     *
     * @param User $patient
     */
    public function __construct(User $patient)
    {
        $this->patient          = $patient;
        $this->practiceSettings = $patient
            ->primaryPractice
            ->cpmSettings();
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
