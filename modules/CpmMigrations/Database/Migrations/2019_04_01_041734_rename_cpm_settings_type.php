<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Settings;
use Illuminate\Database\Migrations\Migration;

class RenameCpmSettingsType extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Settings::where('settingsable_type', 'CircleLinkHealth\Customer\Entities\Practice')
            ->update(
                [
                    'settingsable_type' => 'App\Practice',
                ]
            );
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Settings::where('settingsable_type', 'App\Practice')
            ->update(
                [
                    'settingsable_type' => 'CircleLinkHealth\Customer\Entities\Practice',
                ]
            );
    }
}
