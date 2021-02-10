<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReasonFieldInPatientForcedChargeableServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_forced_chargeable_services', function (Blueprint $table) {
                $table->string('reason')->nullable()->after('action_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_forced_chargeable_services', function (Blueprint $table) {
                $table->dropColumn('reason');
        });
    }
}
