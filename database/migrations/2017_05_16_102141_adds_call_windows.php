<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsCallWindows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'tabular_medical_records',
            'ccd_demographics_logs',
            'demographics_imports',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('preferred_call_times')->nullable();
                $table->string('preferred_call_days')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'tabular_medical_records',
            'ccd_demographics_logs',
            'demographics_imports',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('preferred_call_times');
                $table->dropColumn('preferred_call_days');
            });
        }
    }
}
