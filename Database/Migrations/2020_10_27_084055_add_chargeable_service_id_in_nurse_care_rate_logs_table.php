<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChargeableServiceIdInNurseCareRateLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_care_rate_logs', function (Blueprint $table) {
            $table->dropForeign(['chargeable_service_id']);
            $table->dropColumn('chargeable_service_id');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_care_rate_logs', function (Blueprint $table) {
            $table->unsignedInteger('chargeable_service_id')->nullable();

            $table->foreign('chargeable_service_id')
                ->references('id')
                ->on('chargeable_services')
                ->onDelete('set null');
        });
    }
}
