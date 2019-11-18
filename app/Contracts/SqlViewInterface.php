<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface SqlViewInterface
{
    /**
     * Drop and create Sql Views.
     *
     * @return mixed
     */
    public static function dropAndCreate();
}
