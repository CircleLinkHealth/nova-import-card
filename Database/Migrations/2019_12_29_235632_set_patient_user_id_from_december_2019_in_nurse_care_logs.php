<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class SetPatientUserIdFromDecember2019InNurseCareLogs extends Migration
{
    const ACTIVITY_NEW_NOTE = 'Patient Note Creation';
    const CALL_REACHED      = 'reached';
    const NOTE_COMPLETE     = 'complete';
    const QUERY_FROM_DATE   = '2019-12-01';

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('nurse_care_rate_logs')
            ->where('created_at', '>=', \Carbon\Carbon::parse(self::QUERY_FROM_DATE))
            ->update([
                'patient_user_id' => null,
            ]);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('nurse_care_rate_logs')
            ->where('created_at', '>=', \Carbon\Carbon::parse(self::QUERY_FROM_DATE))
            ->orderBy('created_at')
            ->chunk(50, function (Illuminate\Support\Collection $list) {
                $list->each(function ($record) {
                    $activityId = $record->activity_id;
                    $activity = DB::table('lv_activities')->whereExists(function ($query) {
                        $query->select('id')
                            ->from('users')
                            ->whereRaw('lv_activities.patient_id = users.id');
                    })->find($activityId);
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
     * Copied from {@link \CircleLinkHealth\Customer\NurseTimeAlgorithms\AlternativeCareTimePayableCalculator}.
     *
     * @param Activity $activity
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
}
