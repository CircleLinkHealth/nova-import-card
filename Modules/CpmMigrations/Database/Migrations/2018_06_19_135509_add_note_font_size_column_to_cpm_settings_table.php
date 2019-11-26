<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteFontSizeColumnToCpmSettingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->dropColumn('note_font_size');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->decimal('note_font_size', 2, 1)
                ->nullable()
                ->after('efax_audit_reports');
        });
    }
}
