<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoSoftRejectedInCareAmbassadorLogs extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('care_ambassador_logs', 'no_soft_rejected')) {
            Schema::table('care_ambassador_logs', function (Blueprint $table) {
                $table->dropColumn('no_soft_rejected');
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            $table->integer('no_soft_rejected')
                ->default(0)
                ->after('no_rejected');
        });
    }
}
