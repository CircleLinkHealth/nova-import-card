<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Support\Facades\Log;

class ModifyPatientActivity
{
    private array $activityIds;

    private string $chargeableService;

    /**
     * ModifyPatientActivity constructor.
     */
    public function __construct(string $chargeableService, array $activityIds)
    {
        $this->chargeableService = $chargeableService;
        $this->activityIds       = $activityIds;
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
}
