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
            $table->dropColumn(['chargeable_month', 'chargeable_time', 'status', 'patient_closed_ccm_status', 'id']);
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
            $table->date('chargeable_month')->nullable()->after('chargeable_type');
            $table->unsignedInteger('chargeable_time')->default(0)->after('chargeable_month');
            $table->string('status')->nullable()->after('chargeable_time');
            $table->string('patient_closed_ccm_status')->nullable()->after('chargeable_time');
            //todo: use computed columns
//            $table->unsignedInteger('no_of_successful_calls')->default(0)->after('chargeable_time');
//            $table->unsignedInteger('no_of_calls')->default(0)->after('chargeable_time');
        });
    }
}
