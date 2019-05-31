<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface HasAttachment
{
    /**
     * Returns an Eloquent model.
     *
     * @return Model|null
     */
    public function getAttachment(): ?Model;
}
