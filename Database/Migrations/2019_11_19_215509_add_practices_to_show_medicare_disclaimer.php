<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\AppConfig\PracticesRequiringMedicareDisclaimer;
use Illuminate\Database\Migrations\Migration;

class AddPracticesToShowMedicareDisclaimer extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (isProductionEnv()) {
            $practiceName = 'rocky-mountain-health-centers-south';
        } else {
            $practiceName = 'demo';
        }

        AppConfig::create([
            'config_key'   => PracticesRequiringMedicareDisclaimer::PRACTICE_REQUIRES_MEDICARE_DISCLAIMER_NOVA_KEY,
            'config_value' => $practiceName,
        ]);
    }
}
