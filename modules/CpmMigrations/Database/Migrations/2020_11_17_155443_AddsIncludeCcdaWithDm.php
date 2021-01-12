<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsIncludeCcdaWithDm extends Migration
{
    public function down()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->dropColumn('include_ccda_with_dm');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->boolean('include_ccda_with_dm')->after('dm_awv_reports')->default(0);
        });
    }
}
