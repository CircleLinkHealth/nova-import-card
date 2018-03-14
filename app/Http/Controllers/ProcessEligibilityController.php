<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEligibilityFromGoogleDrive;
use App\Services\CCD\ProcessEligibilityService;

class ProcessEligibilityController extends Controller
{
    /**
     * @var ProcessEligibilityService
     */
    private $processEligibilityService;

    public function __construct(ProcessEligibilityService $processEligibilityService)
    {
        $this->processEligibilityService = $processEligibilityService;
    }

    public function fromGoogleDrive($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems)
    {
        $this->processEligibilityService
            ->queueFromGoogleDrive($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems);

        return "Processing eligibility has been scheduled, and will process in the background.";
    }
    public function fromGoogleDriveDownloadedLocally($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems)
    {
        return $this->processEligibilityService
            ->handleAlreadyDownloadedZip($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems);
    }
}
