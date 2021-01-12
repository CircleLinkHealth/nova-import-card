<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseLoadCapacityToNurseInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->dropColumn('case_load_capacity');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->integer('case_load_capacity')->nullable()->after('high_rate');
        });
    }
}
