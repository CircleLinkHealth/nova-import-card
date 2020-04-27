<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Collection;

abstract class EnrollableService
{
    /**
     * @var Enrollee
     */
    protected $enrollee;
    /**
     * @var int
     */
    protected $enrolleeId;
    
    /**
     * @var Collection|null
     */
    protected $data;
    
    public function __construct($enrolleeId, Collection $data = null)
    {
        $this->enrolleeId = $enrolleeId;
        $this->data = $data ?? collect([]);
    }

    protected function getModel()
    {
        $this->enrollee = Enrollee::with(['confirmedFamilyMembers'])->findOrFail($this->enrolleeId);
    }
}
