<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCareAmbassadorHourlyRateToDecimal extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_ambassadors', function (Blueprint $table) {
            $table->unsignedInteger('hourly_rate')->nullable()->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_ambassadors', function (Blueprint $table) {
            $table->decimal('hourly_rate', 8, 2)->nullable()->change();
        });
    }
}
