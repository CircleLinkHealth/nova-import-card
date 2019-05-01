<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportSettingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('report_settings');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('report_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('value');
            $table->timestamps();
        });

        DB::table('report_settings')->insert([
            [
                'name'        => 'nurse_report_successful',
                'description' => 'Nurse Efficiency Index successful calls multiplier. Used in Nurses and States Report, and Nurse Daily Performance Emails',
                'value'       => '0.25',
            ],
            [
                'name'        => 'nurse_report_unsuccessful',
                'description' => 'Nurse Efficiency Index unsuccessful calls multiplier. Used in Nurses and States Report, and Nurse Daily Performance Emails',
                'value'       => '0.067',
            ],
            [
                'name'        => 'time_goal_per_billable_patient',
                'description' => 'Average Care Coach time per Billable Patient. Used in Nurses and States Report, Nurse Daily Performance Emails, and Ops Dashboard Reports',
                'value'       => '25',
            ],
        ]);
    }
}
