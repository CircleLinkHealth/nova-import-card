<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClinicalEscalationPhoneInLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('locations', 'clinical_escalation_phone')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->string('clinical_escalation_phone')
                      ->after('phone')
                      ->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ( ! Schema::hasColumn('locations', 'clinical_escalation_phone')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropColumn('clinical_escalation_phone');
            });
        }
    }
}
