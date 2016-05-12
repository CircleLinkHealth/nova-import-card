<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeTypeUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET foreign_key_checks = 0');
            \App\CarePlanTemplate::truncate();
        DB::statement('SET foreign_key_checks = 1');

        Schema::table('care_plan_templates', function (Blueprint $table) {
            $table->string('type')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_plan_templates', function (Blueprint $table) {
            $table->dropUnique(['type']);
        });
    }
}
