<?php

namespace App\CLH\DataTemplates;

abstract class BaseDataTemplate
{
    public function getArray()
    {
        return get_object_vars($this);
    }
}
