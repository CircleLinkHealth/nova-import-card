<?php

use App\Location;
use App\Practice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPracticeIdToLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'practice_id')) {
                return;
            }

            $table->unsignedInteger('practice_id')
                ->after('id');

            $table->foreign('practice_id')
                ->references('id')
                ->on('practices')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        foreach (Practice::all() as $practice) {
            if ($practice->locationId() == 0) {
                continue;
            }

            $rootLoc = Location::find($practice->locationId());

            $rootLoc->practice_id = $practice->id;
            $rootLoc->save();

            $children = Location::whereParentId($rootLoc->id)->get();

            foreach ($children as $loc) {
                $loc->practice_id = $practice->id;
                $loc->save();
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
        Schema::table('locations', function (Blueprint $table) {
            //
        });
    }
}
