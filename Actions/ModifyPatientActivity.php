<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Actions;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Support\Facades\Log;

class ModifyPatientActivity
{
    private array $activityIds;

    private string $chargeableService;

    private ?Carbon $month;

    private ?string $sourceChargeableService;

    private function __construct(string $chargeableService, array $activityIds = [], ?Carbon $month = null, ?string $sourceChargeableService = null)
    {
        $this->chargeableService       = $chargeableService;
        $this->activityIds             = $activityIds;
        $this->month                   = $month;
        $this->sourceChargeableService = $sourceChargeableService;
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
            $this->setActivityIds();
        }

        if (empty($this->activityIds)) {
            throw new \Exception("could not find activity ids using parameters: month[$this->month] & chargeable service[$this->sourceChargeableService]");
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
    }

    public static function forActivityIds(string $chargeableService, array $activityIds): self
    {
        return new ModifyPatientActivity($chargeableService, $activityIds);
    }

    public static function forMonth(string $chargeableService, Carbon $month, string $current = null): self
    {
        return new ModifyPatientActivity($chargeableService, [], $month, $current);
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

    private function setActivityIds()
    {
        $sourceCsId = $this->sourceChargeableService
            ? ChargeableService::cached()->firstWhere('code', '=', $this->sourceChargeableService)->id
            : null;

        $this->activityIds = Activity::whereBetween('performed_at', [$this->month->copy()->startOfMonth(), $this->month->copy()->endOfMonth()])
            ->when( ! is_null($sourceCsId), fn ($q) => $q->where('chargeable_service_id', '=', $sourceCsId))
            ->pluck('id')
            ->toArray();
    }
}
