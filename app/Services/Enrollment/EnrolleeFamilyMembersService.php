<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;


use App\SafeRequest;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

abstract class EnrolleeFamilyMembersService
{
    /**
     * @var integer
     */
    protected $enrolleeId;

    /**
     * @var Enrollee
     */
    protected $enrollee;

    public function __construct($enrolleeId)
    {
        $this->enrolleeId = $enrolleeId;
    }

    protected function getModel()
    {
        $this->enrollee = Enrollee::with(['confirmedFamilyMembers'])->findOrFail($this->enrolleeId);
    }
}