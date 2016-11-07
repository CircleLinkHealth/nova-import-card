<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_problems', function (Blueprint $table) {
            $table->dropForeign(['care_item_id']);
            $table->dropForeign(['care_item_name']);
            $table->dropColumn('care_item_id');
            $table->dropColumn('care_item_name');
        });

        $tables = [
            'lv_careplans',
            'care_plans',
            'care_plan_care_section',
            'care_item_care_plan',
            'care_item_user_values',
        ];

        foreach ($tables as $t) {
            echo "Dropping $t" . PHP_EOL;

            Schema::table($t, function (Blueprint $table) {
                DB::statement("SET foreign_key_checks=0");
                $table->dropIfExists();
                DB::statement("SET foreign_key_checks=1");
            });
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
