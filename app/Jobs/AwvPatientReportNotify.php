<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AwvPatientReportNotify implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $patientReportData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $patientReportdata)
    {
        $this->patientReportData = $patientReportdata;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //find patient, load billing provider and practice and settings

        //from practice settings instantiate sender classes and dispatch jobs
    }
}
