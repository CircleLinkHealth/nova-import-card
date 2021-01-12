<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpmSettingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_settings');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'cpm_settings',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('settingsable_id')->unsigned();
                $table->string('settingsable_type');
                $table->enum('careplan_mode', ['web', 'pdf'])->default('web');
                $table->boolean('auto_approve_careplans')->default(0);
                $table->boolean('dm_pdf_careplan')->default(1);
                $table->boolean('dm_pdf_notes')->default(1);
                $table->boolean('dm_audit_reports');
                $table->boolean('email_careplan_approval_reminders')->default(1);
                $table->boolean('email_note_was_forwarded')->default(1);
                $table->boolean('email_weekly_report')->default(1);
                $table->boolean('efax_pdf_careplan')->default(1);
                $table->boolean('efax_pdf_notes')->default(1);
                $table->boolean('efax_audit_reports');
                $table->string('default_target_bp', 7)->default('130/80');
                $table->enum('bill_to', ['practice', 'provider'])->default('practice');
                $table->integer('default_chargeable_service_id')->unsigned()->nullable()->default(1);
                $table->timestamps();
                $table->unique(
                    ['settingsable_id', 'settingsable_type'],
                    'settings_settingsable_id_settingsable_type_unique'
                );
            }
        );
    }
}
