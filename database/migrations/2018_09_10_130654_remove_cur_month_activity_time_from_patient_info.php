<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCurMonthActivityTimeFromPatientInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dropColumn('cur_month_activity_time');
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
            $table->unsignedInteger('cur_month_activity_time')
                  ->default(0);
        });
    }
}
