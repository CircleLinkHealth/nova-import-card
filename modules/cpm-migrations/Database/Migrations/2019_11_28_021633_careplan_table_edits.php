<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CareplanTableEdits extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'care_plan_templates_cpm_biometrics',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );

        Schema::table(
            'care_plan_templates_cpm_lifestyles',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );

        Schema::table(
            'care_plan_templates_cpm_medication_groups',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );

        Schema::table(
            'care_plan_templates_cpm_miscs',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );

        Schema::table(
            'care_plan_templates_cpm_problems',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );

        Schema::table(
            'care_plan_templates_cpm_symptoms',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );
    }
}
