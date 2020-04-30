<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVirtualColumnToCcda extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            $table->dropColumn('patient_first_name');
            $table->dropColumn('patient_last_name');
            $table->dropColumn('patient_mrn');
            $table->dropColumn('patient_dob');
            $table->dropColumn('patient_email');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            $table->string('patient_first_name')->virtualAs('REPLACE(
                 REPLACE(
                    REPLACE(
                        JSON_UNQUOTE(json->"$.demographics.name.given"),
                        \'[\', \'\'
                    ),
                    \']\', \'\'
                 ),
                 \'"\', \'\'
             )')->index();

            $table->string('patient_last_name')->virtualAs('JSON_UNQUOTE(json->"$.demographics.name.family")')->index();
            $table->string('patient_mrn')->virtualAs('JSON_UNQUOTE(json->"$.demographics.mrn_number")')->index();
            $table->string('patient_dob')->virtualAs('DATE(JSON_UNQUOTE(json->"$.demographics.dob"))')->index();
            $table->string('patient_email')->nullable()->virtualAs('IF(JSON_UNQUOTE(json->"$.demographics.email") = "null", NULL, JSON_UNQUOTE(json->"$.demographics.email"))')->index();
        });
    }
}
