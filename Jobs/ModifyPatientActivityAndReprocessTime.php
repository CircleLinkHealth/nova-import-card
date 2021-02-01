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
use function Composer\Autoload\includeFile;

class ModifyPatientActivityAndReprocessTime implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $fromCsId;
    private Carbon $month;
    private array $patientIds;
    private string $toCsCode;
    private bool $legacy;

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

        (new ModifyPatientActivity($this->toCsCode, $activityIds->toArray()))->execute();

        if ($this->legacy){
            (app(ActivityService::class))->processMonthlyActivityTime($this->patientIds, $this->month);
        }
    }
}
