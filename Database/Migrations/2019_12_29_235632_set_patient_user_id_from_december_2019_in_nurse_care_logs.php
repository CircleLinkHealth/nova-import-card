<?php

use Illuminate\Database\Migrations\Migration;

class SetPatientUserIdFromDecember2019InNurseCareLogs extends Migration
{
    const ACTIVITY_NEW_NOTE = 'Patient Note Creation';
    const NOTE_COMPLETE = 'complete';
    const CALL_REACHED = 'reached';

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
                  $activity   = DB::table('lv_activities')->find($activityId);
                  if ( ! $activity) {
                      return;
                  }

                  $isSuccessfulCall = $this->isActivityForSuccessfulCall($activity);

                  DB::table('nurse_care_rate_logs')
                    ->where('id', '=', $record->id)
                    ->update([
                        'patient_user_id'    => $activity->patient_id,
                        'is_successful_call' => $isSuccessfulCall,
                    ]);
              });
          });
    }

    /**
     * Copied from {@link \App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator}
     *
     * @param Activity $activity
     *
     * @return bool
     */
    private function isActivityForSuccessfulCall($activity): bool
    {
        if (self::ACTIVITY_NEW_NOTE !== $activity->type) {
            return false;
        }

        $performedAt = Carbon::parse($activity->performed_at);
        $noteIds     = DB::table('notes')
                         ->whereBetween('performed_at', [
                             $performedAt->copy()->startOfDay(),
                             $performedAt->copy()->endOfDay(),
                         ])
                         ->where('status', '=', self::NOTE_COMPLETE)
                         ->where('author_id', '=', $activity->logger_id)
                         ->where('patient_id', '=', $activity->patient_id)
                         ->pluck('id');

        $hasSuccessfulCall = false;
        if ( ! empty($noteIds)) {
            $hasSuccessfulCall = DB::table('calls')->whereIn('note_id', $noteIds)
                                   ->where('status', '=', self::CALL_REACHED)
                                   ->count() > 0;
        }

        return $hasSuccessfulCall;
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
