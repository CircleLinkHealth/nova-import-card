<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Actions;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Illuminate\Support\Facades\Log;

class ModifyPatientActivity
{
    private array $activityIds = [];

    private ?string $chargeableService = null;

    private ?Carbon $month = null;

    private array $patientIds = [];

    private ?string $sourceChargeableService = null;

    private function __construct()
    {
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        /** @var ChargeableService $cs */
        $cs = ChargeableService::cached()->where('code', '=', $this->chargeableService)->first();
        if ( ! $cs) {
            throw new \Exception("could not find chargeable service $this->chargeableService");
        }

        if (empty($this->activityIds)) {
            $this->fillActivityIds();
        }

        if (empty($this->activityIds)) {
            throw new \Exception("could not find activity ids using parameters: month[$this->month] & chargeable service[$this->sourceChargeableService]");
        }

        if (empty($this->month)) {
            /** @var Activity $firstActivity */
            $firstActivity = Activity::find($this->activityIds[0]);
            $this->setMonth(Carbon::parse($firstActivity->performed_at)->startOfMonth());
        }

        Activity::whereIn('id', $this->activityIds)
            ->update([
                'chargeable_service_id' => $cs->id,
            ]);

        NurseCareRateLog::whereIn('activity_id', $this->activityIds)
            ->update([
                'chargeable_service_id' => $cs->id,
            ]);

        $this->generateNurseInvoices();

        $this->processLegacyPms();
    }

    public static function forActivityIds(string $chargeableService, array $activityIds): self
    {
        return (new ModifyPatientActivity())
            ->setChargeableService($chargeableService)
            ->setActivityIds($activityIds);
    }

    public static function forMonth(string $chargeableService, Carbon $month, string $current = null): self
    {
        return (new ModifyPatientActivity())
            ->setChargeableService($chargeableService)
            ->setMonth($month)
            ->setSourceChargeableService($current);
    }

    public function setActivityIds(array $activityIds): ModifyPatientActivity
    {
        $this->activityIds = $activityIds;

        return $this;
    }

    public function setChargeableService(string $chargeableService): ModifyPatientActivity
    {
        $this->chargeableService = $chargeableService;

        return $this;
    }

    public function setMonth(?Carbon $month): ModifyPatientActivity
    {
        $this->month = $month;

        return $this;
    }

    public function setSourceChargeableService(?string $sourceChargeableService): ModifyPatientActivity
    {
        $this->sourceChargeableService = $sourceChargeableService;

        return $this;
    }

    private function fillActivityIds()
    {
        $sourceCsId = $this->getSourceCsId();
        Activity::whereBetween('performed_at', [$this->month->copy()->startOfMonth(), $this->month->copy()->endOfMonth()])
            ->when( ! is_null($sourceCsId), fn ($q) => $q->where('chargeable_service_id', '=', $sourceCsId))
            ->get(['id', 'patient_id'])
            ->each(function (Activity $activity) {
                $this->activityIds[] = $activity->id;
                $this->patientIds[] = $activity->patient_id;
            });
    }

    private function fillPatientIds() : void
    {
        if (! empty($this->activityIds)){
            $this->patientIds = Activity::whereIn('id', $this->activityIds)
                                        ->pluck('patient_id')
                                        ->toArray();
            return;
        }
        $this->fillActivityIds();
    }

    private function generateNurseInvoices()
    {
        NurseCareRateLog::with('nurse')
            ->whereIn('activity_id', $this->activityIds)
            ->distinct('nurse_id')
            ->select('nurse_id')
            ->each(function (NurseCareRateLog $nurseLog) {
                $nurseUserId = $nurseLog->nurse->user_id;
                Log::debug("Ready to regenerate invoice for $nurseUserId");
                \Artisan::call('nurseinvoices:create', [
                    'month'   => now()->startOfMonth()->toDateString(),
                    'userIds' => $nurseUserId,
                ]);
            });
    }

    private function getSourceCsId(): ?int
    {
        return $this->sourceChargeableService
            ? ChargeableService::cached()->firstWhere('code', '=', $this->sourceChargeableService)->id
            : null;
    }

    private function processLegacyPms()
    {
        if (empty($this->patientIds)) {
            $this->fillPatientIds();
        }
        app(ActivityService::class)->processMonthlyActivityTime($this->patientIds, $this->month);
    }

    public function setPatientIds(array $patientIds):self
    {
        $this->patientIds = $patientIds;
        return $this;
    }

    private function getTargetCsId() : ?int
    {
        return $this->chargeableService
            ? ChargeableService::cached()->firstWhere('code', '=', $this->chargeableService)->id
            : null;
    }
}
