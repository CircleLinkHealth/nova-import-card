<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFulfilledColumnOnChargeablesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chargeables', function (Blueprint $table) {
            if (Schema::hasColumn('chargeables', 'is_fulfilled')) {
                $table->dropColumn('is_fulfilled');
            }
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chargeables', function (Blueprint $table) {
            if ( ! Schema::hasColumn('chargeables', 'is_fulfilled')) {
                $table->boolean('is_fulfilled')->default(1)->after('amount');
            }
        });
    }
}
