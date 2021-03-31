<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;


use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Actions\ModifyPatientActivity;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ModifyPatientActivityAndReprocessTime implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $fromCsId;
    private bool $legacy;
    private Carbon $month;
    private array $patientIds;
    private string $toCsCode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $patientIds, Carbon $month, int $fromCsId, string $toCsCode, bool $legacy = false)
    {
        $this->patientIds = $patientIds;
        $this->month      = $month;
        $this->fromCsId   = $fromCsId;
        $this->toCsCode   = $toCsCode;
        $this->legacy     = $legacy;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $activityIds = Activity::whereIn('patient_id', $this->patientIds)
            ->where('chargeable_service_id', $this->fromCsId)
            ->createdInMonth($this->month, 'performed_at')
            ->pluck('id');

        ModifyPatientActivity::forActivityIds($this->toCsCode, $activityIds->toArray())
                             ->setMonth($this->month)
                             ->setPatientIds($this->patientIds)
                             ->execute();
    }
}
