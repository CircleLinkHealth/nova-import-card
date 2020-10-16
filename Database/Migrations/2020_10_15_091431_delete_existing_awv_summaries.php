<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Migrations\Migration;

class DeleteExistingAwvSummaries extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $awvCodeIds = ChargeableService::whereIn('code', [
            ChargeableService::AWV_INITIAL,
            ChargeableService::AWV_SUBSEQUENT,
        ])
            ->pluck('id')
            ->toArray();

        ChargeablePatientMonthlySummary::whereIn('chargeable_service_id', $awvCodeIds)
            ->where('chargeable_month', Carbon::parse('2020-10-01'))
            ->delete();
    }
}
