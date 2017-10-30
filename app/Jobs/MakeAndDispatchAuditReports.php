<?php

namespace App\Jobs;

use App\CLH\Helpers\StringManipulation;
use App\Contracts\Efax;
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
     * An instance of PhiMail
     *
     * @var PhiMail
     */
    protected $phiMail;

    /**
     * @var Efax
     */
    protected $eFax;

    /**
     * @var Carbon
     */
    protected $date;

    /**
     * Create a new job instance.
     *
     * @param User $patient
     * @param Carbon $date
     * @param Efax|null $eFax
     * @param PhiMail|null $phiMail
     *
     */
    public function __construct(User $patient, Carbon $date = null, Efax $eFax = null, PhiMail $phiMail = null)
    {
        $this->patient = $patient;
        $this->date = $date ?? Carbon::now();
        $this->phiMail = $phiMail ?? new PhiMail();
        $this->eFax = $eFax ?? new PhaxioService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = (new PatientDailyAuditReport($this->patient->patientInfo,
            $this->date->startOfMonth()))
            ->renderPDF();

        $path = storage_path("download/$fileName");

        if (!$path) {
            \Log::error("File not found: $path");
            return;
        }

        $settings = $this->patient->primaryPractice->settings->firstOrNew();

        $sent = $this->patient->locations->get()->map(function ($location) use ($path, $settings, $fileName) {
            //Send DM mail
            if ($settings->dm_audit_reports) {
                $this->phiMail->send($location->emr_direct_address, $path, $fileName);
            }

            //Send eFax
            $fax = $location->fax;

            if ($settings->efax_audit_reports && $fax) {
                $number = (new StringManipulation())->formatPhoneNumberE164($fax);
                $this->eFax->send($number, $path);
            }

            return $location;
        });
    }
}
