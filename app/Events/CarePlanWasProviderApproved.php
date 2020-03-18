<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/19/20
 * Time: 2:27 PM
 */

namespace App\Events;


use CircleLinkHealth\Customer\Entities\User;

class CarePlanWasProviderApproved extends Event
{
    /**
     * @var User
     */
    public $patient;
    
    /**
     * CarePlanWasProviderApproved constructor.
     *
     * @param User $patient
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }

}