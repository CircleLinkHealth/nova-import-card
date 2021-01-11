<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use CircleLinkHealth\Customer\Entities\CustomerNotificationContactTimePreference;

trait HasNotificationContactPreferences
{
    public function notificationContactPreferences()
    {
        return $this->morphMany(CustomerNotificationContactTimePreference::class, 'notificationContactPreferencable', 'contactable_type', 'contactable_id');
    }
}
