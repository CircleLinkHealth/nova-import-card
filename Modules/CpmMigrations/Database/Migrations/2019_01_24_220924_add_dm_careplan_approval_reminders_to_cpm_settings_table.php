<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDmCareplanApprovalRemindersToCpmSettingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('cpm_settings', 'dm_careplan_approval_reminders')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->dropColumn('dm_careplan_approval_reminders');
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('cpm_settings', 'dm_careplan_approval_reminders')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->boolean('dm_careplan_approval_reminders')->default(0)->after('dm_audit_reports');
            });
        }
    }
}
