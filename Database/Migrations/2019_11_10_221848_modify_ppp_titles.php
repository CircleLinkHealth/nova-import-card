<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ModifyPppTitles extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = 'ppp_task_recommendations';

        DB::table($table)
            ->where('title', 'Tobacco / Smoking')
            ->update(['title' => 'Tobacco/Smoking']);

        DB::table($table)
            ->where('title', 'Weight / BMI')
            ->update(['title' => 'Weight/BMI']);

        DB::table($table)
            ->where('title', 'Immunizations / Vaccines')
            ->update(['title' => 'Immunizations/Vaccines']);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'ppp_task_recommendations';

        DB::table($table)
            ->where('title', 'Tobacco/Smoking')
            ->update(['title' => 'Tobacco / Smoking']);

        DB::table($table)
            ->where('title', 'Weight/BMI')
            ->update(['title' => 'Weight / BMI']);

        DB::table($table)
            ->where('title', 'Immunizations/Vaccines')
            ->update(['title' => 'Immunizations / Vaccines']);
    }
}
