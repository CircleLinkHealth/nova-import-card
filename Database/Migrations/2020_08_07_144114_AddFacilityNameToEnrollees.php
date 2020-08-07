<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFacilityNameToEnrollees extends Migration
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
        if (Schema::hasColumn($table = (new Enrollee())->getTable(), 'facility_name')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->string('facility_name')
                ->nullable()->after('location_id');
        });
    }
}
