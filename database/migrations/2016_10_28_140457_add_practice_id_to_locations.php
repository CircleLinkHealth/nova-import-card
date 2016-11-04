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
                ->default(4)
                ->after('id');
        });

        foreach (Practice::all() as $practice) {
            if ($practice->locationId() == 0) {
                continue;
            }

            $rootLoc = Location::find($practice->locationId());

            if (!$rootLoc) {
                continue;
            }

            $rootLoc->practice_id = 4;
            $rootLoc->save();

            $children = Location::whereParentId($rootLoc->id)->get();

            if ($children->isEmpty()) {
                $rootLoc->practice_id = $practice->id;
                $rootLoc->save();
            }

            foreach ($children as $loc) {
                $loc->practice_id = $practice->id;
                $loc->save();
            }
        }

        Schema::table('locations', function (Blueprint $table) {
            DB::statement('set foreign_key_checks = 0');

            $table->foreign('practice_id')
                ->references('id')
                ->on('practices')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            DB::statement('set foreign_key_checks = 1');
        });
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
