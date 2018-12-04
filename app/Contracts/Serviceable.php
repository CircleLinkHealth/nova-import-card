<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface Serviceable
{
    /**
     * Get this Model's Service Class.
     *
     * @return Serviceable
     */
    public function service();
}
