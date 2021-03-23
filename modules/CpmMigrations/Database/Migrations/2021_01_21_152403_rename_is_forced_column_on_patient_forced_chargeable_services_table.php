<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameIsForcedColumnOnPatientForcedChargeableServicesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_forced_chargeable_services', function (Blueprint $table) {
            $table->dropColumn('action_type');

            $table->boolean('is_forced')->default(true)->after('chargeable_month');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_forced_chargeable_services', function (Blueprint $table) {
            $table->dropColumn('is_forced');

            $table->enum('action_type', ['force', 'block'])->default('force')->after('chargeable_month');
        });
    }
}