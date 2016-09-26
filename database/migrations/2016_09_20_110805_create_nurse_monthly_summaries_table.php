<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNurseMonthlySummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurse_monthly_summaries', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('nurse_id');
            $table->date('month_year');
            $table->integer('time')->nullable();
            $table->integer('ccm_time')->nullable();
            $table->integer('no_of_calls')->nullable();
            $table->integer('no_of_successful_calls')->nullable();
            $table->timestamps();

            $table->foreign('nurse_id')
                ->references('id')
                ->on('nurse_info')
                ->onUpdate('cascade')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('nurse_monthly_summaries', function (Blueprint $table) {
//
//            $table->dropColumn('nurse_id');
//            $table->dropColumn('month_year');
//            $table->dropColumn('time');
//            $table->dropColumn('ccm_time');
//            $table->dropColumn('no_of_calls');
//            $table->dropColumn('no_of_successful_calls');
//
//        });
    }
}
