<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisitFeeToNurseInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('nurse_info', 'visit_fee')) {
            Schema::table('nurse_info', function (Blueprint $table) {
                $table->dropColumn('visit_fee');
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
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->float('visit_fee')
                ->default(12.50)
                ->nullable(true)
                ->after('high_rate');
        });
    }
}
