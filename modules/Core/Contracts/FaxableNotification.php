<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

interface FaxableNotification
{
    public function toFax($notifiable = null): array;
}
