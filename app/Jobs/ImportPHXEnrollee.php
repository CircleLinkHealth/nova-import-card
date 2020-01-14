<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use App\Services\MedicalRecords\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportPHXEnrollee implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var Enrollee
     */
    private $enrollee;

    /**
     * Create a new job instance.
     *
     * @param \CircleLinkHealth\Eligibility\Entities\Enrollee $enrollee
     */
    public function __construct(Enrollee $enrollee)
    {
        $this->enrollee = $enrollee;
    }

    /**
     * Execute the job.
     */
    public function handle(ImportService $importService)
    {
        return $importService->importPHXEnrollee($this->enrollee);
    }
}
