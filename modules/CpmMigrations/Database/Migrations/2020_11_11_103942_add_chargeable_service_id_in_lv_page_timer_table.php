<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChargeableServiceIdInLvPageTimerTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('lv_page_timer', 'chargeable_service_id')) {
            Schema::table('lv_page_timer', function (Blueprint $table) {
                $table->dropForeign(['chargeable_service_id']);

                $table->dropColumn('chargeable_service_id');
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('lv_page_timer', 'chargeable_service_id')) {
            Schema::table('lv_page_timer', function (Blueprint $table) {
                $table->unsignedInteger('chargeable_service_id')->nullable()->after('provider_id');

                $table->foreign('chargeable_service_id')
                    ->references('id')
                    ->on('chargeable_services')
                    ->onDelete('set null');
            });
        }
    }
}