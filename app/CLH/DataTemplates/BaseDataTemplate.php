<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\DataTemplates;

abstract class BaseDataTemplate
{
    public function getArray()
    {
        return get_object_vars($this);
    }
}
