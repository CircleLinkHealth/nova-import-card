<?php

namespace App\Jobs;

use App\CLH\Helpers\StringManipulation;
use App\Reports\PatientDailyAuditReport;
use App\Services\Phaxio\PhaxioService;
use App\Services\PhiMail\PhiMail;
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

        //send DM message
        $dmAddress = $this->patient->locations->first()->emr_direct_address;

        if ($this->patient->primaryPractice->settings->first()->dm_audit_reports && $dmAddress) {
            $phiMail = new PhiMail();
            $test = $phiMail->send($dmAddress, $path);
        }

        //send eFax
        $fax = $this->patient->locations->first()->fax;

        if ($this->patient->primaryPractice->settings->first()->efax_audit_reports && $fax) {
            $number = (new StringManipulation())->formatPhoneNumberE164($fax);
            $faxTest = (new PhaxioService())->send($number, $path);
        }
    }
}
