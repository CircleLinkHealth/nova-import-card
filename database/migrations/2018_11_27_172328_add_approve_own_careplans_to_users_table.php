<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApproveOwnCareplansToUsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            $table->dropColumn('approve_own_care_plans');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            $table->boolean('approve_own_care_plans')->after('specialty')->default(0);
        });
    }
}
