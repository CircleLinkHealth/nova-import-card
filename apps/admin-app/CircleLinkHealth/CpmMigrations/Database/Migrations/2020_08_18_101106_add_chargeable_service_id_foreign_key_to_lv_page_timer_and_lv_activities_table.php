<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChargeableServiceIdForeignKeyToLvPageTimerAndLvActivitiesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lv_activities', function (Blueprint $table) {
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
        Schema::table('lv_activities', function (Blueprint $table) {
            $table->unsignedInteger('chargeable_service_id')->nullable()->after('comment_id');

            $table->foreign('chargeable_service_id')
                ->references('id')
                ->on('chargeable_services')
                ->onDelete('set null');
        });
    }
}
