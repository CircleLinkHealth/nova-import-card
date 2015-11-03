<?php

namespace App\CLH\DataTemplates;


abstract class DataTemplate
{
    public function getArray()
    {
        return get_object_vars($this);
    }
}