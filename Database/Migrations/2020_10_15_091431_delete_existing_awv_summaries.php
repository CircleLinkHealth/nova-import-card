<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use Carbon\Carbon;

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
