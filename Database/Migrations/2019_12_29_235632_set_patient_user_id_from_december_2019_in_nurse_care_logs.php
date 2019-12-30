<?php

use Illuminate\Database\Migrations\Migration;

class SetPatientUserIdFromDecember2019InNurseCareLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('nurse_care_rate_logs')
          ->where('created_at', '>=', \Carbon\Carbon::parse('2019-12-01'))
          ->orderBy('created_at')
          ->chunk(50, function (\Illuminate\Support\Collection $list) {
              $list->each(function ($record) {
                  $activityId = $record->activity_id;
                  $activity   = DB::table('lv_activities')->find($activityId, ['patient_id']);
                  if ( ! $activity) {
                      return;
                  }

                  DB::table('nurse_care_rate_logs')
                    ->where('id', '=', $record->id)
                    ->update([
                        'patient_user_id' => $activity->patient_id,
                    ]);
              });
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('nurse_care_rate_logs')
          ->where('created_at', '>=', \Carbon\Carbon::parse('2019-12-01'))
          ->update([
              'patient_user_id' => null,
          ]);

    }
}
