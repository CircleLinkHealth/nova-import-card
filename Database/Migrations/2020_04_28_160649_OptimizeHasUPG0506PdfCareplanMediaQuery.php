<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OptimizeHasUPG0506PdfCareplanMediaQuery extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('is_pdf');
            $table->dropColumn('is_ccda');
            $table->dropColumn('is_upg0506');
            $table->dropColumn('is_upg0506_complete');
            $table->dropColumn('mrn');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->boolean('is_pdf')->virtualAs('IF(JSON_UNQUOTE(custom_properties->"$.is_pdf") = "true", TRUE, FALSE)')->before('created_at')->index();
            $table->boolean('is_ccda')->virtualAs('IF(JSON_UNQUOTE(custom_properties->"$.is_ccda") = "true", TRUE, FALSE)')->before('created_at')->index();
            $table->boolean('is_upg0506')->virtualAs('IF(JSON_UNQUOTE(custom_properties->"$.is_upg0506") = "true", TRUE, FALSE)')->before('created_at')->index();
            $table->boolean('is_upg0506_complete')->virtualAs('IF(JSON_UNQUOTE(custom_properties->"$.is_upg0506_complete") = "true", TRUE, FALSE)')->before('created_at')->index();
            $table->string('mrn')->virtualAs('JSON_UNQUOTE(custom_properties->"$.care_plan.demographics.mrn_number")')->before('created_at')->index();
        });
    }
}
