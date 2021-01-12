<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAwvColumnsToCpmSettingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->dropColumn('dm_awv_reports');
            $table->dropColumn('email_awv_reports');
            $table->dropColumn('efax_awv_reports');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->boolean('dm_awv_reports')->after('dm_careplan_approval_reminders')->default(0);
            $table->boolean('email_awv_reports')->after('email_weekly_report')->default(0);
            $table->boolean('efax_awv_reports')->after('efax_audit_reports')->default(0);
        });
    }
}
