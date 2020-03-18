<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/19/20
 * Time: 1:23 PM
 */

namespace App\Events;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Queue\SerializesModels;


class CarePlanWasQAApproved extends Event
{
    use SerializesModels;
    /**
     * @var User
     */
    public $patient;
    
    
    /**
     * CarePlanWasQAApproved constructor.
     *
     * @param User $patient
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
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