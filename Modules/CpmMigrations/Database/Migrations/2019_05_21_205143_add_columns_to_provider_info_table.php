<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProviderInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            $table->dropColumn('pronunciation');
            $table->dropColumn('sex');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            $table->string('pronunciation')->after('prefix')->nullable();
            $table->string('sex')->after('prefix')->nullable();
        });
    }
}
