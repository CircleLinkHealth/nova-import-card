<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Enrollment;

use App\Enrollee;

class EnrolleeSuggestedFamilyMembers
{
    protected $enrollee;

    public function __construct(Enrollee $enrollee)
    {
        $this->enrollee = $enrollee;
    }

    //create static method?

    public function get()
    {
        //get suggested family members

        //format data for view

        //return array
    }
}
