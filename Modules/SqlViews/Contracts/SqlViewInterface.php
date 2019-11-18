<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SqlViews\Contracts;

interface SqlViewInterface
{
    /**
     * Drop and create Sql Views.
     *
     * @return mixed
     */
    public static function dropAndCreate();
}
