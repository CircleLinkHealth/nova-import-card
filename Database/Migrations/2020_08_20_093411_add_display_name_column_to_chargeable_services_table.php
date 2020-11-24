<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayNameColumnToChargeableServicesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('chargeable_services', 'display_name')) {
            Schema::table('chargeable_services', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('chargeable_services', 'display_name')) {
            Schema::table('chargeable_services', function (Blueprint $table) {
                $table->string('display_name')->nullable()->after('code');
            });
        }
    }
}
