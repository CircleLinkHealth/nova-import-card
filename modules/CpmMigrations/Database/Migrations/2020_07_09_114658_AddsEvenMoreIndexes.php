<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsEvenMoreIndexes extends Migration
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
        //for UnreachablesFinalAction@query
        Schema::table('users', function (Blueprint $table) {
            $table->index([
                'id',
                'deleted_at',
            ]);
        });

        //for CallController@getPatientNextScheduledCallJson
        Schema::table('calls', function (Blueprint $table) {
            $table->string('status')->change();
        });

        Schema::table('calls', function (Blueprint $table) {
            $table->index([
                'inbound_cpm_id',
                'status',
                'scheduled_date',
                'type',
            ]);
        });
    }
}
