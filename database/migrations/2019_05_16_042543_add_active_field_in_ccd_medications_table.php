<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveFieldInCcdMedicationsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ccd_medications', function (Blueprint $table) {
            if (Schema::hasColumn('ccd_medications', 'active')) {
                $table->dropColumn('active');
            }
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ccd_medications', function (Blueprint $table) {
            $table->boolean('active')
                ->nullable(true)
                ->default(true);
        });

        //https://stackoverflow.com/questions/46657614/laravel-5-4-update-all-records-in-table-without-using-where-clause
        DB::table('ccd_medications')
            ->where('id', 'id')
            ->update(['active' => 1]);
    }
}
