<?php

use App\Location;
use App\User;
use Illuminate\Database\Migrations\Migration;

class MigrateUsersLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (User::exceptType('participant')->get() as $user) {
            $locations = Location::whereIn('practice_id', $user->viewableProgramIds())
                ->pluck('id');

            foreach ($locations as $id) {
                try {
                    $user->locations()->attach($id);
                } catch (\Exception $e) {
                    Log::alert("Location $id for user id {$user->id} was not added because {$e->getMessage()}.");
                }
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
        //
    }
}
