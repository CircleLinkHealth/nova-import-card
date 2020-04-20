<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Http\Controllers;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
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
                ->handleAlreadyDownloadedZip(
                    $request['dir'],
                    $request['practiceName'],
                    $request['filterLastEncounter'],
                    $request['filterInsurance'],
                    $request['filterProblems']
                );
        } elseif ((bool) $request->get('file')) {
            $practice = Practice::whereName($request['practiceName'])->firstOrFail();

            $batch = $this->processEligibilityService
                ->createClhMedicalRecordTemplateBatch(
                    $request['dir'],
                    $request['file'],
                    $practice->id,
                    $request['filterLastEncounter'],
                    $request['filterInsurance'],
                    $request['filterProblems']
                );
        } else {
            $practice = Practice::whereName($request['practiceName'])->firstOrFail();

            $batch = $this->processEligibilityService
                ->createGoogleDriveCcdsBatch(
                    $request['dir'],
                    $practice->id,
                    $request['filterLastEncounter'],
                    $request['filterInsurance'],
                    $request['filterProblems']
                );
        }

        return redirect()->route('eligibility.batch.show', [$batch->id]);
    }

    public function fromGoogleDriveDownloadedLocally(
        $dir,
        $practiceName,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        return $this->processEligibilityService
            ->handleAlreadyDownloadedZip($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems);
    }
}
