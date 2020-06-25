<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Reports\PatientDailyAuditReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateAuditReportForPatientForMonth implements ShouldQueue
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
        $fileName = (new PatientDailyAuditReport(
            $this->patient,
            $this->date->startOfMonth()
        ))
            ->renderPDF();

        \File::delete(storage_path("download/${fileName}"));
    }
}
