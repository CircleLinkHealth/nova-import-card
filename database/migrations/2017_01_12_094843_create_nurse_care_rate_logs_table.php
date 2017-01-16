<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

            $table->unsignedInteger('nurse_id')->required();
            $table->unsignedInteger('activity_id')->nullable();
            $table->string('ccm_type')->required();
            $table->integer('increment')->required();
            $table->timestamps();

            $table->foreign('nurse_id')
                ->references('id')
                ->on('nurse_info')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('activity_id')
                ->references('id')
                ->on('lv_activities')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_care_rate_logs', function (Blueprint $table) {

            $table->drop();

        });
    }
}
