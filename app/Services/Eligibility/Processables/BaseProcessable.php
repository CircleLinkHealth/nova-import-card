<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/22/2018
 * Time: 7:20 PM
 */

namespace App\Services\Eligibility\Processables;

use App\Contracts\EligibilityProcessable;
use App\Jobs\ProcessEligibilityProcessable;
use App\Practice;
use Illuminate\Http\File;

abstract class BaseProcessable implements EligibilityProcessable
{
    /**
     * @var bool
     */
    public $createEnrollees;

    /**
     * @var
     */
    private $file;

    /**
     * @var Practice
     */
    public $practice;

    /**
     * @var bool
     */
    public $filterLastEncounter;

    /**
     * @var bool
     */
    public $filterInsurance;

    /**
     * @var bool
     */
    public $filterProblems;

    /**
     * BaseProcessable constructor.
     *
     * @param $file
     * @param Practice $practice
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
     * Queue a file to process for eligibility.
     */
    public function queue()
    {
        ProcessEligibilityProcessable::dispatch($this);
    }

    abstract public function processEligibility();

    /**
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getFilePath()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
}
