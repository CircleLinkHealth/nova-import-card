<?php

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Nova\Actions\ModifyPatientActivity;
use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TransferTimeFromCsForLegacyABP implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $patientIds;
    protected Carbon $month;
    protected int $fromCsId;
    protected int $toCsId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $patientIds, Carbon $month, int $fromCsId, int $toCsId)
    {
        $this->patientIds = $patientIds;
        $this->month = $month;
        $this->fromCsId = $fromCsId;
        $this->toCsId = $toCsId;
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
                               ->createdInMonth($this->month,'performed_at')
                               ->pluck('id');

        (new ModifyPatientActivity($this->toCsId, $activityIds))->execute();

        (app(ActivityService::class))->processMonthlyActivityTime($this->patientIds, $this->month);
    }
}
