<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTimezoneToLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lv_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('lv_locations', 'timezone')) {
                $table->text('timezone')->after('state')->nullable();
            }
        });

        // populate correct timezone
        $locations = \App\Location::all();
        if($locations->count() > 0) {
            // first set timestamps
            foreach($locations as $location) {
                $location->created_at = date('Y-m-d H:i:s');
                $location->updated_at = date('Y-m-d H:i:s');
                $location->timezone = 'America/New_York';
                $location->save();
            }

            // next set timezone
            foreach($locations as $location) {
                $baseLocation = $location;
                if(!empty($location->parent)) {
                    $baseLocation = $location->parent;
                    if(!empty($location->parent->parent)) {
                        $baseLocation = $location->parent->parent;
                    }
                }
                if(!$baseLocation) {
                    echo $location->id . ' - NO BASE LOCATION' . PHP_EOL;
                    continue 1;
                }

                $program = \App\Practice::where('location_id', '=', $baseLocation->id)->first();
                if(!$program) {
                    echo $location->id . 'NO PROGRAM FOR LOCATION' . PHP_EOL;
                    continue 1;
                }

                echo $program->name . PHP_EOL;

                /*
                - Monheit is MT
                - Rocky Mountain is MT
                - Mazhar is CT
                */
                $timezone = 'America/New_York';
                if($program->name == 'monheit') {
                    $timezone = 'America/Denver';
                } else if($program->name == 'rockymountainhealthcenters') {
                    $timezone = 'America/Denver';
                } else if($program->name == 'mazhar') {
                    $timezone = 'America/Chicago';
                }

                $location->timezone = $timezone;
                $location->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lv_locations', function (Blueprint $table) {
            if (Schema::hasColumn('lv_locations', 'timezone')) {
                $table->dropColumn('timezone');
            }
        });
    }
}
