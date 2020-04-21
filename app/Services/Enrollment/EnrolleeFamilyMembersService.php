<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\Eligibility\Entities\Enrollee;

abstract class EnrolleeFamilyMembersService
{
    /**
     * @var Enrollee
     */
    protected $enrollee;
    /**
     * @var int
     */
    protected $enrolleeId;

    public function __construct($enrolleeId)
    {
        $this->enrolleeId = $enrolleeId;
    }

    protected function getModel()
    {
        $this->enrollee = Enrollee::with(['confirmedFamilyMembers'])->findOrFail($this->enrolleeId);
    }
}
