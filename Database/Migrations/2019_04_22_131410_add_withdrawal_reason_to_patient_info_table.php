<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWithdrawalReasonToPatientInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dropColumn('withdrawn_reason');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->text('withdrawn_reason')->after('date_withdrawn')->nullable();
        });
    }
}
