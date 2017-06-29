<?php

namespace App\Jobs;

use App\Reports\PatientDailyAuditReport;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeAndDispatchAuditReports implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The Patient we are preparing a report for.
     *
     * @var User
     */
    protected $patient;

    /**
     * Create a new job instance.
     *
     * @param User $patient
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = (new PatientDailyAuditReport($this->patient->patientInfo,
            Carbon::now()->subMonth()->startOfMonth()))
            ->renderPDF();

        $path = storage_path("download/$fileName");


    }
}
