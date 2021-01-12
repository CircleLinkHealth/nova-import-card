<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

class DefaultProblemServicesToLocation
{
    public static function attach(int $locationId): void
    {
        //todo:
        //need practice for pcm
        //bhi and ccm can be taken from CPM problem
        //class CpmProblemService that decides that
        //dispatch different jobs for each Location
        //Each job uses this
        //reusable for event as well
        //create command to do it manually as well
        //test
    }
}
