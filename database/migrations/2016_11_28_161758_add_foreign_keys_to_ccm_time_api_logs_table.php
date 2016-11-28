<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcmTimeApiLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccm_time_api_logs', function (Blueprint $table) {
            $table->foreign('activity_id',
                'activity_id_foreign')->references('id')->on('lv_activities')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccm_time_api_logs', function (Blueprint $table) {
            $table->dropForeign('activity_id_foreign');
        });
    }

}
