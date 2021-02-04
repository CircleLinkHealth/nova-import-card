<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Http\Controllers;

use CircleLinkHealth\Eligibility\Http\Requests\UploadEligibilityCsv;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WelcomeCallListController extends Controller
{
    protected $processEligibilityService;

    public function __construct(
        ProcessEligibilityService $processEligibilityService
    ) {
        ini_set('upload_max_filesize', '250M');
        ini_set('post_max_size', '250M');
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);

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

        \Log::debug('File uploaded');

        $filterLastEncounter = (bool) $request->input('filterLastEncounter');
        $filterInsurance     = (bool) $request->input('filterInsurance');
        $filterProblems      = (bool) $request->input('filterProblems');

        $batch = $this->processEligibilityService
            ->createSingleCSVBatch($practiceId, $filterLastEncounter, $filterInsurance, $filterProblems);

        \Log::debug('Eligibility Service created');

        $results = $this->processEligibilityService->createEligibilityJobFromCsvBatch($batch, $patientListCsv);

        $options           = $batch->options;
        $options['errors'] = $results['errors'] ?? [];
        $batch->options    = $options;
        $batch->save();

        return redirect()->route('eligibility.batch.show', [$batch->id]);
    }
}
