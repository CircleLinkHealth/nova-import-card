<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixYearFieldsInAwvTables extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('survey_instances', function (Blueprint $table) {
            if (Schema::hasColumn('survey_instances', 'year')) {
                $table->dropColumn('year');
                $table->string('name')->after('survey_id');
            } else {
                $table->string('name')
                    ->after('survey_id');
            }

            $table->dateTime('start_date')->nullable()->change();
            $table->dateTime('end_date')->nullable()->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('survey_instances', function (Blueprint $table) {
            if (Schema::hasColumn('survey_instances', 'name')) {
                $table->dropColumn('name');
                $table->unsignedInteger('year')->after('survey_id');
            } else {
                $table->unsignedInteger('year')
                    ->after('survey_id');
            }
        });
    }
}
