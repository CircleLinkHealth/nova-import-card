<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseCareRateLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurse_care_rate_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nurse_id')->unsigned()->index('nurse_care_rate_logs_nurse_id_foreign');
            $table->integer('activity_id')->unsigned()->nullable()->index('nurse_care_rate_logs_activity_id_foreign');
            $table->string('ccm_type');
            $table->integer('increment');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('nurse_care_rate_logs');
    }
}
