<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChargeablePivotIdToCallProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->dropForeign('call_problems_chargeable_pivot_id_foreign');
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
            $table->unsignedInteger('chargeable_pivot_id')->nullable()->after('patient_monthly_summary_id');

            $table->foreign('chargeable_pivot_id')
                ->references('id')
                ->on('chargeables')
                ->onDelete('set null');
        });
    }
}
