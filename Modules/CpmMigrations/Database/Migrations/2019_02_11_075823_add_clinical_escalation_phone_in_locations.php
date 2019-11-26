<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClinicalEscalationPhoneInLocations extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if ( ! Schema::hasColumn('locations', 'clinical_escalation_phone')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropColumn('clinical_escalation_phone');
            });
        }
    }

    /**
     * Run the migrations.
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
}
