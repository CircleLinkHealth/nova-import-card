<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Nova\Actions\ModifyPatientActivity;
use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TransferTimeFromCsForLegacyABP implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected int $fromCsId;
    protected Carbon $month;

    protected array $patientIds;
    protected string $toCsCode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $patientIds, Carbon $month, int $fromCsId, string $toCsCode)
    {
        $this->patientIds = $patientIds;
        $this->month      = $month;
        $this->fromCsId   = $fromCsId;
        $this->toCsCode   = $toCsCode;
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

        (new ModifyPatientActivity($this->toCsCode, $activityIds))->execute();

        (app(ActivityService::class))->processMonthlyActivityTime($this->patientIds, $this->month);
    }
}
