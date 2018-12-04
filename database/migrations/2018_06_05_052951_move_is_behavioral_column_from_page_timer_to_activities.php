<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveIsBehavioralColumnFromPageTimerToActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('lv_activities', 'is_behavioral')) {
            Schema::table('lv_activities', function (Blueprint $table) {
                $table->boolean('is_behavioral')->after('comment_id')->default(0)->nullable();
            });
        }
        if (Schema::hasColumn('lv_page_timer', 'is_behavioral')) {
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
        if (! Schema::hasColumn('lv_page_timer', 'is_behavioral')) {
            Schema::table('lv_page_timer', function (Blueprint $table) {
                $table->boolean('is_behavioral')->after('provider_id')->default(0)->nullable();
            });
        }
        if (Schema::hasColumn('lv_activities', 'is_behavioral')) {
            Schema::table('lv_activities', function (Blueprint $table) {
                $table->dropColumn('is_behavioral');
            });
        }
    }
}
