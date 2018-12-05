<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnApiAutoPullToCpmSettingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->dropColumn('api_auto_pull');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            $table->boolean('api_auto_pull')->default(0)->after('bill_to');
        });
    }
}
