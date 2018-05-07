<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsentDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tabular_medical_records', function (Blueprint $table) {
            $table->date('consent_date')->after('language');
        });

        Schema::table('ccd_demographics_logs', function (Blueprint $table) {
            $table->date('consent_date')->after('language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tabular_medical_records', function (Blueprint $table) {
            $table->dropColumn('consent_date');
        });
        Schema::table('ccd_demographics_logs', function (Blueprint $table) {
            $table->dropColumn('consent_date');
        });
    }
}
