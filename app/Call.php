<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

class Call extends \CircleLinkHealth\SharedModels\Entities\Call
{
    //The only purpose of this class is because it throws an exception
    //when redirecting to /redirect-mark-read for pre-existing notifications with
    //relation to App\Call
}
