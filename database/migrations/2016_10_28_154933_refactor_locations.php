<?php

use App\Location;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Location::whereNull('parent_id')
            ->delete();

        Schema::table('locations', function (Blueprint $table) {

            $table->dropColumn('parent_id');
            $table->dropColumn('position');
            $table->dropColumn('real_depth');
            $table->dropColumn('billing_code');
            $table->dropColumn('location_code');

            $table->dropSoftDeletes();
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
