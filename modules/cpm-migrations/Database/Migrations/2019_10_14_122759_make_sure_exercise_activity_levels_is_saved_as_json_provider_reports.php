<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class MakeSureExerciseActivityLevelsIsSavedAsJsonProviderReports extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $records = DB::table('provider_reports')->get(['id', 'exercise_activity_levels']);
        $records->each(function ($r) {
            $str = json_decode($r->exercise_activity_levels)->name;
            DB::table('provider_reports')->where('id', '=', $r->id)->update(['exercise_activity_levels' => $str]);
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $records = DB::table('provider_reports')->get(['id', 'exercise_activity_levels']);
        $records->each(function ($r) {
            //$trimmed = str_replace('"', '', $r->exercise_activity_levels); //not sure if needed
            $trimmed = $r->exercise_activity_levels;
            $jsonVal = json_encode(['name' => $trimmed]);
            DB::table('provider_reports')->where('id', '=', $r->id)->update(['exercise_activity_levels' => $jsonVal]);
        });
    }
}
