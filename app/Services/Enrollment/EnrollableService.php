<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Support\Collection;

abstract class EnrollableService
{
    /**
     * @var Collection|null
     */
    protected $data;
    /**
     * @var Enrollee
     */
    protected $enrollee;
    /**
     * @var int
     */
    protected $enrolleeId;

    public function __construct($enrolleeId, Collection $data = null)
    {
        $this->enrolleeId = $enrolleeId;
        $this->data       = $data ?? collect([]);
    }

    protected function getModel()
    {
        $this->enrollee = Enrollee::with(['confirmedFamilyMembers'])->findOrFail($this->enrolleeId);
    }
}
