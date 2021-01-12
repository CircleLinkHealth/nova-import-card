<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCallIdNullableInCallProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->unsignedInteger('call_id')->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->unsignedInteger('call_id')->nullable()->change();
        });
    }
}
