<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Eligibility\Processables;

use App\Contracts\EligibilityProcessable;
use App\Jobs\ProcessEligibilityProcessable;
use CircleLinkHealth\Customer\Entities\Practice;

abstract class BaseProcessable implements EligibilityProcessable
{
    /**
     * @var bool
     */
    public $createEnrollees;

    /**
     * @var bool
     */
    public $filterInsurance;

    /**
     * @var bool
     */
    public $filterLastEncounter;

    /**
     * @var bool
     */
    public $filterProblems;

    /**
     * @var Practice
     */
    public $practice;

    /**
     * @var
     */
    private $file;

    /**
     * BaseProcessable constructor.
     *
     * @param $file
     * @param bool $filterLastEncounter
     * @param bool $filterInsurance
     * @param bool $filterProblems
     * @param bool $createEnrollees
     */
    public function __construct(
        $file,
        Practice $practice,
        $filterLastEncounter = true,
        $filterInsurance = true,
        $filterProblems = true,
        $createEnrollees = false
    ) {
        $this->file                = $file;
        $this->practice            = $practice;
        $this->filterLastEncounter = $filterLastEncounter;
        $this->filterInsurance     = $filterInsurance;
        $this->filterProblems      = $filterProblems;
        $this->createEnrollees     = $createEnrollees;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->file;
    }

    abstract public function processEligibility();

    /**
     * Queue a file to process for eligibility.
     */
    public function queue()
    {
        ProcessEligibilityProcessable::dispatch($this);
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
}
