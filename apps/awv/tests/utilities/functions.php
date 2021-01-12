<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

function create($class, $attributes = [], $times = null)
{
    return factory($class, $times)->create($attributes);
}

function make($class, $attributes = [], $times = null)
{
    return factory($class, $times)->make($attributes);
}
