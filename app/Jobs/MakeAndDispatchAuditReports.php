<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\CLH\Helpers\StringManipulation;
use App\Contracts\DirectMail;
use App\Contracts\Efax;
use App\Reports\PatientDailyAuditReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeAndDispatchAuditReports implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @var DirectMail
     */
    protected $directMail;

    /**
     * @var Efax
     */
    protected $eFax;

    /**
     * The Patient we are preparing a report for.
     *
     * @var User
     */
    protected $patient;

    /**
     * Create a new job instance.
     *
     * @param Carbon $date
     */
    public function __construct(User $patient, Carbon $date = null)
    {
        $this->patient    = $patient;
        $this->date       = $date ?? Carbon::now();
        $this->directMail = app(DirectMail::class);
        $this->eFax       = app(Efax::class);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $fileName = (new PatientDailyAuditReport(
            $this->patient->patientInfo,
            $this->date->startOfMonth()
        ))
            ->renderPDF();

        $path = storage_path("download/${fileName}");

        if ( ! is_readable($path)) {
            \Log::error("File not found: ${path}");

            return;
        }

        $settings = $this->patient->primaryPractice->settings()->firstOrNew([]);

        $sent = $this->patient->locations->map(function ($location) use ($path, $settings, $fileName) {
            //Send DM mail
            if ($settings->dm_audit_reports) {
                $this->directMail->send($location->emr_direct_address, $path, $fileName);
            }

            //Send eFax
            $fax = $location->fax;

            if ($settings->efax_audit_reports && $fax) {
                $number = (new StringManipulation())->formatPhoneNumberE164($fax);
                $this->eFax->createFaxFor($number)->send(['file' => $path]);
            }

            return $location;
        });

        \File::delete($path);
    }
}
