<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadEligibilityCsv;
use App\Services\CCD\ProcessEligibilityService;
use Illuminate\Http\Request;

class WelcomeCallListController extends Controller
{
    protected $processEligibilityService;

    public function __construct(
        ProcessEligibilityService $processEligibilityService
    ) {
        $this->processEligibilityService = $processEligibilityService;
    }

    /**
     * Create Phoenix Heart Call List from phoenix_heart_* tables.
     */
    public function makePhoenixHeartCallList()
    {
        $batch = $this->processEligibilityService->createPhoenixHeartBatch();

        return link_to_route(
            'eligibility.batch.show',
            'Job Scheduled. Click here to view progress. Make sure you bookmark the link.',
            [$batch->id]
        );
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return array|string
     */
    public function makeWelcomeCallList(UploadEligibilityCsv $request)
    {
        $practiceId     = $request->input('practice_id');
        $patientListCsv = $request->file('patient_list');

        $filterLastEncounter = (bool) $request->input('filterLastEncounter');
        $filterInsurance     = (bool) $request->input('filterInsurance');
        $filterProblems      = (bool) $request->input('filterProblems');

        $batch = $this->processEligibilityService
            ->createSingleCSVBatch($practiceId, $filterLastEncounter, $filterInsurance, $filterProblems);

        $results = $this->processEligibilityService->createEligibilityJobFromCsvBatch($batch, $patientListCsv);

        $options           = $batch->options;
        $options['errors'] = $results['errors'] ?? [];
        $batch->options    = $options;
        $batch->save();

        return redirect()->route('eligibility.batch.show', [$batch->id]);
    }
}
