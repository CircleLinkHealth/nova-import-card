<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class SetChargeableServiceIdInNurseCareRateLogs extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \CircleLinkHealth\Customer\Entities\NurseCareRateLog::whereBetween('performed_at', [
            Carbon::parse('2020-11-01')->startOfMonth(),
            now()->endOfMonth(),
        ])
            ->each(function (CircleLinkHealth\Customer\Entities\NurseCareRateLog $item) {
                $item->chargeable_service_id = null;
            });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \CircleLinkHealth\Customer\Entities\NurseCareRateLog::whereHas('activity', function ($q) {
            $q->whereNotNull('chargeable_service_id')
                ->select(['id', 'chargeable_service_id']);
        })
            ->whereNull('chargeable_service_id')
            ->whereBetween('performed_at', [
                Carbon::parse('2020-11-01')->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->each(function (CircleLinkHealth\Customer\Entities\NurseCareRateLog $item) {
                $item->chargeable_service_id = $item->activity->chargeable_service_id;
            });
    }
}
