<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPracticeIdToCareAmbassadorLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            $table->dropForeign(['practice_id']);
            $table->dropColumn('practice_id');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            if ( ! Schema::hasColumn('care_ambassador_logs', 'practice_id')) {
                $table->unsignedInteger('practice_id')
                    ->after('enroller_id')
                    ->nullable();

                $table->foreign('practice_id')
                    ->references('id')
                    ->on('practices')
                    ->onUpdate('cascade');
            }
        });
    }
}
