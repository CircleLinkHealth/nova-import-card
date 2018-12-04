<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcmTimeApiLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ccm_time_api_logs', function (Blueprint $table) {
            $table->dropForeign('activity_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ccm_time_api_logs', function (Blueprint $table) {
            $table->foreign('activity_id', 'activity_id_foreign')->references('id')->on('lv_activities')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
