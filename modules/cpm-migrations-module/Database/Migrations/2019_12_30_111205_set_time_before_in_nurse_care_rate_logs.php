<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class SetTimeBeforeInNurseCareRateLogs extends Migration
{
    const QUERY_FROM_DATE = '2019-12-01';

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
                'time_before' => 0,
            ]);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $patientTimes = [];

        DB::table('nurse_care_rate_logs')
            ->where('created_at', '>=', \Carbon\Carbon::parse(self::QUERY_FROM_DATE))
            ->orderBy('ccm_type', 'desc')
            ->orderBy('created_at', 'asc')
            ->chunk(50, function (Illuminate\Support\Collection $list) use (&$patientTimes) {
                $list->each(function ($record) use (&$patientTimes) {
                    if ( ! $record->patient_user_id) {
                        return;
                    }

                    $patientUserId = $record->patient_user_id;
                    if (array_key_exists($patientUserId, $patientTimes)) {
                        $timeBefore = $patientTimes[$patientUserId];
                        DB::table('nurse_care_rate_logs')
                            ->where('id', '=', $record->id)
                            ->update([
                                'time_before' => $timeBefore,
                            ]);
                    }

                    $patientTimes[$patientUserId] = ($patientTimes[$patientUserId] ?? 0) + $record->increment;
                });
            });
    }
}
