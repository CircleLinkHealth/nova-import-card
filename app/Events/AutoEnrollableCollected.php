<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AutoEnrollableCollected
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    /**
     * @var bool
     */
    public $color;
    /**
     * @var bool
     */
    public $isReminder;

    /**
     * @var User
     */
    public $userIds;

    /**
     * AutoEnrollableCollected constructor.
     * @param bool $isReminder
     * @param null $color
     */
    public function __construct(array $userIds, $isReminder = false, $color = null)
    {
        $this->userIds    = $userIds;
        $this->isReminder = $isReminder;
        $this->color      = $color;
    }
}
