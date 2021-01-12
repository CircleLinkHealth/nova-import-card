<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueKeyToLocationProblemServicesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_problem_services', function (Blueprint $table) {
            $table->dropUnique('l_id_cpmp_id_cs_id');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_problem_services', function (Blueprint $table) {
            $table->unique(['location_id', 'cpm_problem_id', 'chargeable_service_id'], 'l_id_cpmp_id_cs_id');
        });
    }
}
