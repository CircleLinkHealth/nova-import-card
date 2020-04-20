<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBhiInNurseCareRateLogs extends Migration
{
    const QUERY_FROM_DATE = '2020-01-01';

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('nurse_care_rate_logs', 'is_behavioral')) {
            Schema::table(
                'nurse_care_rate_logs',
                function (Blueprint $table) {
                    $table->removeColumn('is_behavioral');
                }
            );
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('nurse_care_rate_logs', 'is_behavioral')) {
            Schema::table(
                'nurse_care_rate_logs',
                function (Blueprint $table) {
                    $table->boolean('is_behavioral')->nullable(true)->default(null)->after('is_successful_call');
                }
            );
        }

        DB::table('nurse_care_rate_logs')
            ->where('created_at', '>=', \Carbon\Carbon::parse(self::QUERY_FROM_DATE))
            ->orderBy('created_at')
            ->chunk(
                50,
                function (Illuminate\Support\Collection $list) {
                    $list->each(
                        function ($record) {
                          $activityId = $record->activity_id;
                          $activity = DB::table('lv_activities')->find($activityId);
                          if ( ! $activity) {
                              return;
                          }

                          DB::table('nurse_care_rate_logs')
                              ->where('id', '=', $record->id)
                              ->update(
                                  [
                                      'is_behavioral' => $activity->is_behavioral,
                                  ]
                              );
                      }
                    );
                }
            );
    }
}
