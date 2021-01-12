<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayRatesInNurseInfoForNewAlgo extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->integer('high_rate')->change();
            $table->integer('low_rate')->change();

            if (Schema::hasColumn('nurse_info', 'visit_fee_2')) {
                $table->dropColumn('visit_fee_2');
            }

            if (Schema::hasColumn('nurse_info', 'visit_fee_3')) {
                $table->dropColumn('visit_fee_3');
            }

            if (Schema::hasColumn('nurse_info', 'high_rate_2')) {
                $table->dropColumn('high_rate_2');
            }

            if (Schema::hasColumn('nurse_info', 'high_rate_3')) {
                $table->dropColumn('high_rate_3');
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
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->float('high_rate')
                ->nullable(true)
                ->change();

            $table->float('low_rate')
                ->nullable(true)
                ->change();

            $table->float('visit_fee_3')
                ->default(12.50)
                ->nullable(true)
                ->after('visit_fee');

            $table->float('visit_fee_2')
                ->default(12.50)
                ->nullable(true)
                ->after('visit_fee');

            $table->float('high_rate_3')
                ->default(27.50)
                ->nullable(true)
                ->after('high_rate');

            $table->float('high_rate_2')
                ->default(28.00)
                ->nullable(true)
                ->after('high_rate');
        });
    }
}
