<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCareAmbassadorLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            $table->foreign('enroller_id')->references('id')->on('care_ambassadors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            $table->dropForeign('care_ambassador_logs_enroller_id_foreign');
        });
    }
}
