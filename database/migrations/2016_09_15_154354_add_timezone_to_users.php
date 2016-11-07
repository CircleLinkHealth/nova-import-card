<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTimezoneToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->text('timezone')->after('zip')->nullable();
            }
        });

        // populate timezone based on location
        $users = \App\User::withTrashed()->get();
        if($users->count() > 0) {
            foreach($users as $user) {
                $timezone = 'America/New_York';
                if($user->patientInfo && $user->patientInfo->preferred_contact_location) {
                    $location = \App\Location::find($user->preferred_contact_location);
                    echo $user->id . ' location = ' . $user->preferred_contact_location . PHP_EOL;
                    if($location) {
                        $timezone = $location->timezone;
                    }
                }
                echo $user->id . ' - ' . $timezone . PHP_EOL;
                $user->timezone = $timezone;
                $user->save();
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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'timezone')) {
                $table->dropColumn('timezone');
            }
        });
    }
}
