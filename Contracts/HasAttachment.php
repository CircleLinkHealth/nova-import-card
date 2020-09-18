<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

use Illuminate\Database\Eloquent\Model;

interface HasAttachment
{
    /**
     * Returns an Eloquent model.
     */
    public function getAttachment(): ?Model;
}
