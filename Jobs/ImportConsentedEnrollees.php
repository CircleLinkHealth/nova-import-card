<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\CcdaImporter\ImportEnrollee;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportConsentedEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\EligibilityBatch
     */
    private $batch;
    /**
     * @var array
     */
    private $enrolleeIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $enrolleeIds, EligibilityBatch $batch = null)
    {
        $this->enrolleeIds = $enrolleeIds;
        $this->batch       = $batch;
    }

    /**
     * Execute the job.
     *
     * @param \CircleLinkHealth\Eligibility\ProcessEligibilityService $importService
     */
    public function handle()
    {
        Enrollee::whereIn('id', $this->enrolleeIds)
            ->with(['targetPatient', 'practice', 'eligibilityJob'])
            ->chunkById(
                10,
                function ($enrollees) {
                    $enrollees->each(
                        function ($enrollee) {
                                ImportEnrollee::import($enrollee);
                            }
                    );
                }
            );
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        $ids = implode(',', $this->enrolleeIds);

        return ['importconsentedenrollees', 'enrollees:'.$ids];
    }
}
