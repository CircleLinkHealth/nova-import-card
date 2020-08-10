<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthAndTimeToChargeablesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chargeables', function (Blueprint $table) {
            $table->dropColumn(['month_year', 'time', 'status']);
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chargeables', function (Blueprint $table) {
            $table->primary('id');
            $table->date('month_year')->nullable()->after('amount');
            $table->unsignedInteger('time')->default(0)->after('month_year');
            $table->string('status')->nullable()->after('time');
            //maybe use a relationship count query?
//            $table->unsignedInteger('no_of_successful_calls')->default(0)->after('time');
        });
    }
}
