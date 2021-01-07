<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Reports\PatientDailyAuditReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateAuditReportForPatientForMonth implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var Carbon
     */
    private $date;

    /**
     * @var User
     */
    private $patient;

    /**
     * Create a new job instance.
     */
    public function __construct(User $patient, Carbon $date)
    {
        $this->patient = $patient;
        $this->date    = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \File::delete(
            (new PatientDailyAuditReport(
                $this->patient,
                $this->date->startOfMonth()
            ))
                ->renderPDF()['path']
        );
    }
}
