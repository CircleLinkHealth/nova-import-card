<?php

use CircleLinkHealth\Customer\Entities\Settings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCpmSettingsType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
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
    
    /**
     * Reverse the migrations.
     *
     * @return void
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
}
