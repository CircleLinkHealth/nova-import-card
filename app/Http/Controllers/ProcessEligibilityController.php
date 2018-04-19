<?php

namespace App\Http\Controllers;

use App\Services\CCD\ProcessEligibilityService;
use Illuminate\Http\Request;

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

    public function fromGoogleDrive(Request $request)
    {
        if ($request['localDir']) {
            $this->processEligibilityService
                ->handleAlreadyDownloadedZip($request['dir'], $request['practiceName'], $request['filterLastEncounter'], $request['filterInsurance'], $request['filterProblems']);
        } else {
            $this->processEligibilityService
                ->queueFromGoogleDrive($request['dir'], $request['practiceName'], $request['filterLastEncounter'], $request['filterInsurance'], $request['filterProblems']);
        }

        return "Processing eligibility has been scheduled, and will process in the background.";
    }

    public function fromGoogleDriveDownloadedLocally($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems)
    {
        return $this->processEligibilityService
            ->handleAlreadyDownloadedZip($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems);
    }
}
