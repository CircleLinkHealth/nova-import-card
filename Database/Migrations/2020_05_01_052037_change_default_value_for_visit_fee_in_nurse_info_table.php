<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ChangeDefaultValueForVisitFeeInNurseInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_info', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->float('visit_fee_2')->default(12.00)->change();
            $table->float('visit_fee_3')->default(11.50)->change();
        });

        DB::table('nurse_info')
            ->update(['visit_fee_2' => 12.00, 'visit_fee_3' => 11.50]);
    }
}
