<?php

use App\Activity;
use App\PageTimer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveIsBehavioralColumnFromPageTimerToActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('lv_activities', 'is_behavioral')) {
            Schema::table('lv_activities', function (Blueprint $table) {
                $table->boolean('is_behavioral')->after('comment_id')->default(0)->nullable();
            });
        }
        if (Schema::hasColumn('lv_page_timer', 'is_behavioral')) {
            Activity::whereHas('pageTime', function ($q) {
                return $q->where('lv_page_timer.is_behavioral', 1);
            })->get()->map(function ($activity) {
                $activity->is_behavioral = 1;
                $activity->save();
            });
            Schema::table('lv_page_timer', function (Blueprint $table) {
                $table->dropColumn('is_behavioral');
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
        if (!Schema::hasColumn('lv_page_timer', 'is_behavioral')) {
            Schema::table('lv_page_timer', function (Blueprint $table) {
                $table->boolean('is_behavioral')->after('provider_id')->default(0)->nullable();
            });
        }
        if (Schema::hasColumn('lv_activities', 'is_behavioral')) {
            Activity::where('is_behavioral', 1)->with('pageTime')->get()->map(function ($activity) {
                if ($activity->pageTime) {
                    $activity->pageTime->is_behavioral = 1;
                    $activity->pageTime->save();
                }
            });
            Schema::table('lv_activities', function (Blueprint $table) {
                $table->dropColumn('is_behavioral');
            });
        }
    }
}
