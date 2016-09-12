<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePatientInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {

            $table->text('general_comment');

            $table->integer('preferred_calls_per_month')->default(2);

            $table->date('last_successful_contact_time');

            $table->time('daily_contact_window_start')->default('09:00:00');
            $table->time('daily_contact_window_end')->default('18:00:00');

            $table->unsignedInteger('next_call_id')->nullable();

            $table->foreign('next_call_id')
                  ->references('id')->on('calls')
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
        Schema::table('patient_info', function (Blueprint $table) {

            $table->dropColumn('general_comment');
            $table->dropColumn('last_successful_contact_time');
            $table->dropColumn('next_call_id');
            $table->dropColumn('daily_contact_window_start');
            $table->dropColumn('daily_contact_window_end');
            

        });
    }
}
