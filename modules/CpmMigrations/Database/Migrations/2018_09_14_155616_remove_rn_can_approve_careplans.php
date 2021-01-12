<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveRnCanApproveCareplans extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if ( ! Schema::hasColumn('cpm_settings', 'rn_can_approve_careplans')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->boolean('rn_can_approve_careplans')->default(0);
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumn('cpm_settings', 'rn_can_approve_careplans')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->dropColumn('rn_can_approve_careplans');
            });
        }
    }
}
