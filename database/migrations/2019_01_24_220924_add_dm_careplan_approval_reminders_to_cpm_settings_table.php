<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDmCareplanApprovalRemindersToCpmSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('cpm_settings', 'dm_careplan_approval_reminders')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->boolean('dm_careplan_approval_reminders')->default(0)->after('dm_audit_reports');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('cpm_settings', 'dm_careplan_approval_reminders')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->dropColumn('dm_careplan_approval_reminders');
            });
        }
    }
}
